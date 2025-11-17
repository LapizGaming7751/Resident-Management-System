<?php
// Unified API for Finals_CheckInSystem
// Fixed version with proper announcement CRUD operations

// Include secure configuration
require_once 'config.php';

include("phpqrcode/qrlib.php");

// Disable error display for API responses to prevent HTML in JSON
error_reporting(E_ALL);
ini_set('display_errors', 0); // Changed to 0 to prevent HTML errors in JSON response
ini_set('log_errors', 1); // Log errors instead of displaying them

// Get database connection using secure configuration
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed: " . $e->getMessage()]);
    exit;
}

// Include email functions
// Include email configuration
if (file_exists('email_config.php')) {
    include_once 'email_config.php';
} else {
    // Fallback if email_config.php doesn't exist
    function sendPasswordResetEmail($email, $token, $user_type = 'resident') {
        error_log("email_config.php not found - using fallback email function");
        return false;
    }
}

// Fallback email functions for invite codes
if (!function_exists('sendInviteEmail')) {
    function sendInviteEmail($email, $invite_code, $user_type, $room_code = null, $expires_at = null) {
        // For now, just log the invite details instead of sending email
        error_log("Invite email would be sent to: $email, Code: $invite_code, Type: $user_type, Room: $room_code, Expires: $expires_at");
        
        // In a real implementation, you would send the email here
        // For testing purposes, we'll return false to indicate email wasn't sent
        return false;
    }
}

configureSecureSession();
header("Content-Type: application/json");
// Secure CORS configuration - only allow specific origins
$allowed_origins = [
    'https://siewyaoying.synergy-college.org',
    'https://siewyaoying.synergy-college.org/ResidentManagementSystem'
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // Default to the main domain for same-origin requests
    header("Access-Control-Allow-Origin: https://siewyaoying.synergy-college.org");
}
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'OPTIONS') {
    http_response_code(200);
    exit;
}

$requestData = json_decode(file_get_contents("php://input"), true);
$time = date("Y-m-d H:i:s");

// --- Helper Functions ---
function fetchList($conn, $sql, $params = []) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('SQL Prepare Error: ' . $conn->error . ' | Query: ' . $sql);
        http_response_code(500);
        echo json_encode(["message" => "Database prepare error: " . $conn->error, "error" => true]);
        exit;
    }
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $list = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $list[] = $row;
        }
        $stmt->close();
        return $list;
    } else {
        error_log('SQL Execute Error: ' . $stmt->error . ' | Query: ' . $sql);
        $stmt->close();
        http_response_code(500);
        echo json_encode(["message" => "Database execute error: " . $stmt->error, "error" => true]);
        exit;
    }
}

function fetchMessages($conn, $sender_id, $sender_type, $receiver_id, $receiver_type) {
    $stmt = $conn->prepare("SELECT * FROM messages WHERE ((sender_id = ? AND sender_type = ? AND receiver_id = ? AND receiver_type = ?) OR (sender_id = ? AND sender_type = ? AND receiver_id = ? AND receiver_type = ?)) ORDER BY created_at ASC");
    $stmt->bind_param('isisisis', $sender_id, $sender_type, $receiver_id, $receiver_type, $receiver_id, $receiver_type, $sender_id, $sender_type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $list = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $list[] = $row;
        }
    } else {
        error_log('SQL Error: ' . $stmt->error);
        http_response_code(500);
        echo json_encode(["message" => "Database error: " . $stmt->error, "error" => true]);
        exit;
    }
    $stmt->close();
    return $list;
}

function login($conn, $table, $user, $pass, $session_keys) {
    // Validate table name to prevent SQL injection
    $allowed_tables = ['residents', 'admins', 'security'];
    if (!in_array($table, $allowed_tables)) {
        return ["message" => "Invalid table name", "error" => true];
    }
    
    $stmt = $conn->prepare("SELECT * FROM $table WHERE user = ?");
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $row = $result->fetch_assoc();
        if ($row && password_verify($pass, $row['pass'])) {
            foreach ($session_keys as $key => $val) {
                $_SESSION[$key] = $row[$val];
            }
            $_SESSION['type'] = $table;
            $stmt->close();
            return ["message" => "Welcome, " . $row['user'] . ".", "error" => false];
        }
        $stmt->close();
        return ["message" => ucfirst($table) . " not found", "error" => true];
    }
    $stmt->close();
    return ["message" => "Query failed: " . $conn->error, "error" => true];
}

function errorResponse($msg) {
    http_response_code(500);
    echo json_encode(["message" => $msg, "error" => true]);
    exit;
}

// These functions are deprecated - use prepared statements instead
// Keeping for backward compatibility but they should not be used for new code
function insertRow($conn, $sql) {
    error_log("WARNING: insertRow() function is deprecated. Use prepared statements instead.");
    if ($conn->query($sql)) {
        return ["message" => "Insert successful", "error" => false];
    }
    return ["message" => "Insert failed: " . $conn->error, "error" => true];
}

function updateRow($conn, $sql) {
    error_log("WARNING: updateRow() function is deprecated. Use prepared statements instead.");
    if ($conn->query($sql)) {
        return ["message" => "Update successful", "error" => false];
    }
    return ["message" => "Update failed: " . $conn->error, "error" => true];
}

function deleteRow($conn, $sql) {
    error_log("WARNING: deleteRow() function is deprecated. Use prepared statements instead.");
    if ($conn->query($sql)) {
        return ["message" => "Delete successful", "error" => false];
    }
    return ["message" => "Delete failed: " . $conn->error, "error" => true];
}

// --- Main API Logic ---
switch ($method) {
    case "GET":
        $type = $_GET['type'] ?? '';
        $fetch = $_GET['fetch'] ?? '';
        $id = $_GET['created_by'] ?? ($_SESSION['id'] ?? '');
        
        // Debug session information
        error_log("GET Request - Type: $type, Fetch: $fetch, ID: $id, Session ID: " . ($_SESSION['id'] ?? 'not set') . ", Session Type: " . ($_SESSION['type'] ?? 'not set'));
        
        // Validate resident/guard ID is numeric
        if ($id !== '' && !ctype_digit(strval($id))) {
            errorResponse("Invalid resident/guard ID: $id");
        }
        if ($type === "resident") {
            if ($fetch === "announcements") {
                $list = fetchList($conn, "SELECT * FROM announcements ORDER BY post_time DESC", []);
                echo json_encode($list !== false ? $list : []);
                exit;
            }elseif ($id && ($fetch === '' || $fetch === 'qr' || !isset($_GET['fetch']))) {
                // QR fetch: ?type=resident&created_by=ID or ?type=resident&created_by=ID&fetch=qr
                $stmt = $conn->prepare("SELECT * FROM codes WHERE created_by = ?");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $list = [];
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $list[] = $row;
                    }
                }
                $stmt->close();
                echo json_encode($list);
            } else if ($id && $fetch) {
                switch ($fetch) {
                    case "notifications":
                        $stmt = $conn->prepare("SELECT * FROM notifications WHERE resident_id = ? AND is_read = 0 ORDER BY created_at DESC");
                        $stmt->bind_param('i', $id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        $list = [];
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                $list[] = $row;
                            }
                        }
                        $stmt->close();
                        echo json_encode($list);
                        break;
                    case "messages":
                        $security_id = $_GET['security_id'] ?? 0;
                        $list = fetchMessages($conn, $id, 'resident', $security_id, 'security');
                        echo json_encode($list !== false ? $list : []);
                        break;
                    case "security_list":
                        $list = fetchList($conn, "SELECT id, user FROM security", []);
                        echo json_encode($list !== false ? $list : []);
                        break;
                    case "messaged_security_list":
                        $stmt = $conn->prepare("SELECT DISTINCT s.id, s.user FROM messages m INNER JOIN security s ON m.sender_id = s.id WHERE m.receiver_id = ? AND m.receiver_type = 'resident' AND m.sender_type = 'security' ORDER BY m.id DESC");
                        $stmt->bind_param('i', $id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        $list = [];
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                $list[] = $row;
                            }
                        }
                        $stmt->close();
                        echo json_encode($list);
                        break;
                    default:
                        errorResponse("Fetch not recognized");
                }
            } else if (isset($_GET['user']) && isset($_GET['pass'])) {
                $resp = login($conn, 'residents', $_GET['user'], $_GET['pass'], ["id" => "id", "user" => "user", "room_code" => "room_code"]);
                echo json_encode($resp);
            } else {
                errorResponse("Resident ID missing or invalid for QR fetch");
            }
        } elseif ($type === "admin") {
            if ($fetch) {
                switch ($fetch) {
                    case "resident":
                        $list = fetchList($conn, "SELECT * FROM residents", []);
                        echo json_encode($list !== false ? $list : []);
                        break;
                    case "admin":
                        $list = fetchList($conn, "SELECT * FROM admins", []);
                        echo json_encode($list !== false ? $list : []);
                        break;
                    case "log":
                        $list = fetchList($conn, "SELECT logs.id, logs.token, logs.scan_time, logs.scan_type, logs.scan_by, security.user AS scanner_username, codes.intended_visitor AS intended_visitor, residents.user AS created_by_username, residents.room_code AS created_by_room FROM logs LEFT JOIN security ON logs.scan_by = security.id LEFT JOIN codes ON logs.token = codes.token LEFT JOIN residents ON codes.created_by = residents.id", []);
                        echo json_encode($list !== false ? $list : []);
                        break;
                    case "security":
                        $list = fetchList($conn, "SELECT * FROM security", []);
                        echo json_encode($list !== false ? $list : []);
                        break;
                    case "announcements":
                        $list = fetchList($conn, "SELECT * FROM announcements ORDER BY post_time DESC", []);
                        echo json_encode($list !== false ? $list : []);
                        break;
                    case "invite_codes":
                        $list = fetchList($conn, "SELECT ic.*, a.user as created_by_name FROM invite_codes ic LEFT JOIN admins a ON ic.created_by = a.id ORDER BY ic.created_at DESC", []);
                        echo json_encode($list !== false ? $list : []);
                        break;
                    case "blacklist":
                        try {
                            $list = fetchList($conn, "SELECT b.*, a.user as created_by_name FROM blacklist b LEFT JOIN admins a ON b.created_by = a.id ORDER BY b.created_at DESC", []);
                            echo json_encode($list !== false ? $list : []);
                        } catch (Exception $e) {
                            echo json_encode(["error" => true, "message" => "Database error: " . $e->getMessage()]);
                        }
                        break;
                    default:
                        errorResponse("Fetch not recognized");
                }
            } else if (isset($_GET['user']) && isset($_GET['pass'])) {
                $resp = login($conn, 'admins', $_GET['user'], $_GET['pass'], ["id" => "id", "user" => "user", "access_level" => "access_level"]);
                echo json_encode($resp);
            }
        } elseif ($type === "security") {
            if ($fetch) {
                switch ($fetch) {
                    case "messages":
                        $security_id = $_SESSION['id'] ?? '';
                        $resident_id = $_GET['resident_id'] ?? 0;
                        $list = fetchMessages($conn, $security_id, 'security', $resident_id, 'resident');
                        echo json_encode($list !== false ? $list : []);
                        break;
                    case "resident_list":
                        $list = fetchList($conn, "SELECT id, user, room_code FROM residents", []);
                        echo json_encode($list !== false ? $list : []);
                        break;
                    default:
                        errorResponse("Fetch not recognized");
                }
            } else if (isset($_GET['user']) && isset($_GET['pass'])) {
                $resp = login($conn, 'security', $_GET['user'], $_GET['pass'], ["id" => "id", "user" => "user"]);
                echo json_encode($resp);
            }
        } elseif ($type === "get_csrf_token") {
            // Generate and return CSRF token
            $token = generateCSRFToken();
            echo json_encode(["csrf_token" => $token, "error" => false]);
        }
        break;
    case "POST":
        $type = $requestData['type'] ?? '';
        
        // CSRF Protection for state-changing operations
        $csrf_protected_operations = [
            'create_resident_invite', 'create_security_invite', 'register_with_invite',
            'register_security', 'register_admin', 'resident', 'guest', 'chat',
            'create_announcement', 'request_password_reset', 'reset_password',
            'admin' // Add admin operations to CSRF protection
        ];
        
        if (in_array($type, $csrf_protected_operations)) {
            $csrf_token = $requestData['csrf_token'] ?? '';
            if (!validateCSRFToken($csrf_token)) {
                http_response_code(403);
                echo json_encode(["message" => "CSRF token validation failed", "error" => true]);
                exit;
            }
        }
        
        // Handle announcement deletion (using POST with action parameter)
        if ($type === "admin" && isset($requestData['fetch']) && $requestData['fetch'] === 'announcement' && isset($requestData['action']) && $requestData['action'] === 'delete') {
            $id = $requestData['id'] ?? '';
            if (empty($id) || !is_numeric($id)) {
                echo json_encode(["message" => "Valid announcement ID is required", "error" => true]);
                exit;
            }
            $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                echo json_encode(["message" => "Delete successful", "error" => false]);
            } else {
                echo json_encode(["message" => "Delete failed: " . $stmt->error, "error" => true]);
            }
            $stmt->close();
            exit;
        }
        
        // Handle blacklist addition
        if ($type === "admin" && isset($requestData['fetch']) && $requestData['fetch'] === 'blacklist') {
            $plate = $requestData['plate'] ?? '';
            $reason = $requestData['reason'] ?? '';
            
            if (empty($plate)) {
                echo json_encode(["message" => "Car plate number is required", "error" => true]);
                exit;
            }
            
            try {
                // Check if plate is already blacklisted
                $checkStmt = $conn->prepare("SELECT id FROM blacklist WHERE blacklisted_car_plate = ?");
                if (!$checkStmt) {
                    echo json_encode(["message" => "Database error: " . $conn->error, "error" => true]);
                    exit;
                }
                
                $checkStmt->bind_param('s', $plate);
                $checkStmt->execute();
                $existingResult = $checkStmt->get_result();
                
                if ($existingResult->num_rows > 0) {
                    echo json_encode(["message" => "Car plate is already blacklisted", "error" => true]);
                    exit;
                }
                
                // Add to blacklist with reason
                $adminId = $_SESSION['id'] ?? 1; // Default to admin ID 1 if session not set
                $insertStmt = $conn->prepare("INSERT INTO blacklist (blacklisted_car_plate, created_by, reason) VALUES (?, ?, ?)");
                if (!$insertStmt) {
                    echo json_encode(["message" => "Database error: " . $conn->error, "error" => true]);
                    exit;
                }
                
                $insertStmt->bind_param('sis', $plate, $adminId, $reason);
                
                if ($insertStmt->execute()) {
                    echo json_encode(["message" => "Car plate added to blacklist successfully", "error" => false]);
                } else {
                    echo json_encode(["message" => "Failed to add car plate to blacklist: " . $insertStmt->error, "error" => true]);
                }
            } catch (Exception $e) {
                echo json_encode(["message" => "Error: " . $e->getMessage(), "error" => true]);
            }
            exit;
        }
        
        if ($type === "create_resident_invite") {
            $email = $requestData['email'] ?? '';
            $room_code = $requestData['room_code'] ?? '';
            $expiry_hours = intval($requestData['expiry_hours'] ?? 24);
            
            // Debug logging
            error_log("Create resident invite - Session ID: " . session_id());
            error_log("Create resident invite - Session data: " . print_r($_SESSION, true));
            
            // Ensure user is logged in as admin
            if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'admin') {
                error_log("Unauthorized - Session ID: " . (isset($_SESSION['id']) ? $_SESSION['id'] : 'not set') . ", Type: " . (isset($_SESSION['type']) ? $_SESSION['type'] : 'not set'));
                echo json_encode(["message" => "Unauthorized", "error" => true]);
                exit;
            }
            
            // Validation
            if (empty($email) || empty($room_code)) {
                echo json_encode(["message" => "Email and room code are required", "error" => true]);
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(["message" => "Invalid email format", "error" => true]);
                exit;
            }
            
            // Check if email already has an active invite
            $checkStmt = $conn->prepare("SELECT id FROM invite_codes WHERE email = ? AND user_type = 'resident' AND is_used = 0 AND expires_at > NOW()");
            $checkStmt->bind_param('s', $email);
            $checkStmt->execute();
            $existingResult = $checkStmt->get_result();
            
            if ($existingResult->num_rows > 0) {
                echo json_encode(["message" => "An active invite already exists for this email", "error" => true]);
                exit;
            }
            $checkStmt->close();
            
            // Generate unique invite code
            $invite_code = strtoupper(bin2hex(random_bytes(8)));
            $expires_at = date('Y-m-d H:i:s', strtotime("+{$expiry_hours} hours"));
            
            $sql = "INSERT INTO invite_codes (code, user_type, email, room_code, created_by, expires_at) VALUES (?, 'resident', ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                echo json_encode(["message" => "Database prepare failed: " . $conn->error, "error" => true]);
                exit;
            }
            
            $stmt->bind_param('sssis', $invite_code, $email, $room_code, $_SESSION['id'], $expires_at);
            
            if ($stmt->execute()) {
                // Send invite email
                $email_sent = sendInviteEmail($email, $invite_code, 'resident', $room_code, $expires_at);
                
                if ($email_sent) {
                    echo json_encode([
                        "message" => "Invite code created and email sent successfully", 
                        "error" => false,
                        "invite_code" => $invite_code,
                        "expires_at" => $expires_at,
                        "email_sent" => true
                    ]);
                } else {
                    echo json_encode([
                        "message" => "Invite code created successfully, but email could not be sent. Please share the code manually.", 
                        "error" => false,
                        "invite_code" => $invite_code,
                        "expires_at" => $expires_at,
                        "email_sent" => false
                    ]);
                }
            } else {
                error_log("Execute failed: " . $stmt->error);
                echo json_encode(["message" => "Failed to create invite code: " . $stmt->error, "error" => true]);
            }
            $stmt->close();
        } elseif ($type === "create_security_invite") {
            $email = $requestData['email'] ?? '';
            $expiry_hours = intval($requestData['expiry_hours'] ?? 24);
            
            // Ensure user is logged in as admin
            if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'admin') {
                echo json_encode(["message" => "Unauthorized", "error" => true]);
                exit;
            }
            
            // Validation
            if (empty($email)) {
                echo json_encode(["message" => "Email is required", "error" => true]);
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(["message" => "Invalid email format", "error" => true]);
                exit;
            }
            
            // Check if email already has an active invite
            $checkStmt = $conn->prepare("SELECT id FROM invite_codes WHERE email = ? AND user_type = 'security' AND is_used = 0 AND expires_at > NOW()");
            $checkStmt->bind_param('s', $email);
            $checkStmt->execute();
            $existingResult = $checkStmt->get_result();
            
            if ($existingResult->num_rows > 0) {
                echo json_encode(["message" => "An active invite already exists for this email", "error" => true]);
                exit;
            }
            $checkStmt->close();
            
            // Generate unique invite code
            $invite_code = strtoupper(bin2hex(random_bytes(8)));
            $expires_at = date('Y-m-d H:i:s', strtotime("+{$expiry_hours} hours"));
            
            $sql = "INSERT INTO invite_codes (code, user_type, email, created_by, expires_at) VALUES (?, 'security', ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                echo json_encode(["message" => "Database prepare failed: " . $conn->error, "error" => true]);
                exit;
            }
            
            $stmt->bind_param('ssis', $invite_code, $email, $_SESSION['id'], $expires_at);
            
            if ($stmt->execute()) {
                // Send invite email
                $email_sent = sendInviteEmail($email, $invite_code, 'security', null, $expires_at);
                
                if ($email_sent) {
                    echo json_encode([
                        "message" => "Invite code created and email sent successfully", 
                        "error" => false,
                        "invite_code" => $invite_code,
                        "expires_at" => $expires_at,
                        "email_sent" => true
                    ]);
                } else {
                    echo json_encode([
                        "message" => "Invite code created successfully, but email could not be sent. Please share the code manually.", 
                        "error" => false,
                        "invite_code" => $invite_code,
                        "expires_at" => $expires_at,
                        "email_sent" => false
                    ]);
                }
            } else {
                error_log("Execute failed: " . $stmt->error);
                echo json_encode(["message" => "Failed to create invite code: " . $stmt->error, "error" => true]);
            }
            $stmt->close();
        } elseif ($type === "request_password_reset") {
            try {
                $email = $requestData['email'] ?? '';
                $user_type = $requestData['user_type'] ?? 'resident';
            
            // Validation
            if (empty($email)) {
                echo json_encode(["message" => "Email is required", "error" => true]);
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(["message" => "Invalid email format", "error" => true]);
                exit;
            }
            
            if (!in_array($user_type, ['resident', 'security', 'admin'])) {
                echo json_encode(["message" => "Invalid user type", "error" => true]);
                exit;
            }
            
            // Check if user exists
            $allowed_tables = ['residents', 'admins', 'security'];
            $table = $user_type === 'admin' ? 'admins' : $user_type . 's';
            if (!in_array($table, $allowed_tables)) {
                echo json_encode(["message" => "Invalid user type", "error" => true]);
                exit;
            }
            $checkStmt = $conn->prepare("SELECT id FROM $table WHERE email = ?");
            $checkStmt->bind_param('s', $email);
            $checkStmt->execute();
            $userResult = $checkStmt->get_result();
            
            if ($userResult->num_rows === 0) {
                // Don't reveal if email exists or not for security
                echo json_encode(["message" => "If an account with that email exists, a password reset link has been sent.", "error" => false]);
                exit;
            }
            
            $user = $userResult->fetch_assoc();
            $user_id = $user['id'];
            $checkStmt->close();
            
            // Invalidate any existing reset tokens for this user
            $stmt = $conn->prepare("UPDATE password_reset_tokens SET is_used = 1 WHERE user_id = ? AND user_type = ?");
            $stmt->bind_param('is', $user_id, $user_type);
            $stmt->execute();
            $stmt->close();
            
            // Generate secure reset token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Insert new reset token
            $sql = "INSERT INTO password_reset_tokens (token, user_type, user_id, email, expires_at) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssiss', $token, $user_type, $user_id, $email, $expires_at);
            
            if ($stmt->execute()) {
                // Send password reset email using PHPMailer
                $email_sent = sendPasswordResetEmail($email, $token, $user_type);
                
                if ($email_sent) {
                    echo json_encode([
                        "message" => "If an account with that email exists, a password reset link has been sent to your email address.", 
                        "error" => false
                    ]);
                } else {
                    echo json_encode([
                        "message" => "Password reset link generated, but there was an issue sending the email. Please contact support.", 
                        "error" => false
                    ]);
                }
            } else {
                echo json_encode(["message" => "Failed to generate reset token: " . $conn->error, "error" => true]);
            }
            $stmt->close();
            } catch (Exception $e) {
                error_log("Password reset error: " . $e->getMessage());
                echo json_encode(["message" => "An error occurred while processing your request. Please try again.", "error" => true]);
            }
        } elseif ($type === "verify_reset_token") {
            $token = $requestData['token'] ?? '';
            
            if (empty($token)) {
                echo json_encode(["message" => "Token is required", "error" => true]);
                exit;
            }
            
            // Check if token is valid and not expired
            $checkStmt = $conn->prepare("SELECT * FROM password_reset_tokens WHERE token = ? AND is_used = 0 AND expires_at > NOW()");
            $checkStmt->bind_param('s', $token);
            $checkStmt->execute();
            $tokenResult = $checkStmt->get_result();
            
            if ($tokenResult->num_rows === 0) {
                echo json_encode(["message" => "Invalid, expired, or already used reset token", "error" => true]);
                exit;
            }
            
            $resetToken = $tokenResult->fetch_assoc();
            $checkStmt->close();
            
            // Verify the email in the token still matches the account
            $table = $resetToken['user_type'] === 'admin' ? 'admins' : $resetToken['user_type'] . 's';
            $verifyStmt = $conn->prepare("SELECT id, email FROM $table WHERE id = ? AND email = ?");
            $verifyStmt->bind_param('is', $resetToken['user_id'], $resetToken['email']);
            $verifyStmt->execute();
            $verifyResult = $verifyStmt->get_result();
            
            if ($verifyResult->num_rows === 0) {
                echo json_encode(["message" => "Account email mismatch. Please request a new password reset.", "error" => true]);
                exit;
            }
            
            $account = $verifyResult->fetch_assoc();
            $verifyStmt->close();
            
            echo json_encode([
                "message" => "Token is valid", 
                "error" => false,
                "email" => $account['email'],
                "user_type" => $resetToken['user_type'],
                "expires_at" => $resetToken['expires_at']
            ]);
        } elseif ($type === "reset_password") {
            $token = $requestData['token'] ?? '';
            $new_password = $requestData['new_password'] ?? '';
            $confirm_password = $requestData['confirm_password'] ?? '';
            
            // Validation
            if (empty($token) || empty($new_password) || empty($confirm_password)) {
                echo json_encode(["message" => "All fields are required", "error" => true]);
                exit;
            }
            
            if ($new_password !== $confirm_password) {
                echo json_encode(["message" => "Passwords do not match", "error" => true]);
                exit;
            }
            
            if (strlen($new_password) < 6) {
                echo json_encode(["message" => "Password must be at least 6 characters long", "error" => true]);
                exit;
            }
            
            // Check if token is valid and not expired
            $checkStmt = $conn->prepare("SELECT * FROM password_reset_tokens WHERE token = ? AND is_used = 0 AND expires_at > NOW()");
            $checkStmt->bind_param('s', $token);
            $checkStmt->execute();
            $tokenResult = $checkStmt->get_result();
            
            if ($tokenResult->num_rows === 0) {
                echo json_encode(["message" => "Invalid, expired, or already used reset token", "error" => true]);
                exit;
            }
            
            $resetToken = $tokenResult->fetch_assoc();
            $checkStmt->close();
            
            // Additional security: Verify the email in the token still matches the account
            $allowed_tables = ['residents', 'admins', 'security'];
            $table = $resetToken['user_type'] === 'admin' ? 'admins' : $resetToken['user_type'] . 's';
            if (!in_array($table, $allowed_tables)) {
                echo json_encode(["message" => "Invalid user type in token", "error" => true]);
                exit;
            }
            $verifyStmt = $conn->prepare("SELECT id, email FROM $table WHERE id = ? AND email = ?");
            $verifyStmt->bind_param('is', $resetToken['user_id'], $resetToken['email']);
            $verifyStmt->execute();
            $verifyResult = $verifyStmt->get_result();
            
            if ($verifyResult->num_rows === 0) {
                echo json_encode(["message" => "Account email mismatch. Please request a new password reset.", "error" => true]);
                exit;
            }
            $verifyStmt->close();
            
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Update password
                $allowed_tables = ['residents', 'admins', 'security'];
                if (!in_array($table, $allowed_tables)) {
                    throw new Exception("Invalid user type in token");
                }
                $updateStmt = $conn->prepare("UPDATE $table SET pass = ? WHERE id = ?");
                $updateStmt->bind_param('si', $hashedPassword, $resetToken['user_id']);
                
                if (!$updateStmt->execute()) {
                    throw new Exception("Failed to update password");
                }
                $updateStmt->close();
                
                // Mark token as used
                $markUsedStmt = $conn->prepare("UPDATE password_reset_tokens SET is_used = 1, used_at = NOW() WHERE id = ?");
                $markUsedStmt->bind_param('i', $resetToken['id']);
                if (!$markUsedStmt->execute()) {
                    throw new Exception("Failed to mark token as used");
                }
                $markUsedStmt->close();
                
                $conn->commit();
                echo json_encode(["message" => "Password reset successfully", "error" => false]);
                
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(["message" => "Password reset failed: " . $e->getMessage(), "error" => true]);
            }
        } elseif ($type === "register_with_invite") {
            $invite_code = $requestData['invite_code'] ?? '';
            $user = $requestData['user'] ?? '';
            $pass = $requestData['pass'] ?? '';
            $email = $requestData['email'] ?? '';
            
            // Validation
            if (empty($invite_code) || empty($user) || empty($pass) || empty($email)) {
                echo json_encode(["message" => "All fields are required", "error" => true]);
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(["message" => "Invalid email format", "error" => true]);
                exit;
            }
            
            // Check if invite code is valid and not expired
            $checkStmt = $conn->prepare("SELECT * FROM invite_codes WHERE code = ? AND email = ? AND is_used = 0 AND expires_at > NOW()");
            $checkStmt->bind_param('ss', $invite_code, $email);
            $checkStmt->execute();
            $inviteResult = $checkStmt->get_result();
            
            if ($inviteResult->num_rows === 0) {
                echo json_encode(["message" => "Invalid, expired, or already used invite code", "error" => true]);
                exit;
            }
            
            $invite = $inviteResult->fetch_assoc();
            $checkStmt->close();
            
            // Check if username already exists in the target table
            $allowed_tables = ['residents', 'security'];
            $table = $invite['user_type'] === 'resident' ? 'residents' : 'security';
            if (!in_array($table, $allowed_tables)) {
                echo json_encode(["message" => "Invalid user type in invite", "error" => true]);
                exit;
            }
            $checkUserStmt = $conn->prepare("SELECT id FROM $table WHERE user = ?");
            $checkUserStmt->bind_param('s', $user);
            $checkUserStmt->execute();
            $userResult = $checkUserStmt->get_result();
            
            if ($userResult->num_rows > 0) {
                echo json_encode(["message" => "Username already exists", "error" => true]);
                exit;
            }
            $checkUserStmt->close();
            
            $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Insert user based on type
                if ($invite['user_type'] === 'resident') {
                    $sql = "INSERT INTO residents (user, pass, email, room_code) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ssss', $user, $hashedPass, $email, $invite['room_code']);
                } else {
                    $sql = "INSERT INTO security (user, pass, email) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('sss', $user, $hashedPass, $email);
                }
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create user account");
                }
                $stmt->close();
                
                // Mark invite as used
                $updateStmt = $conn->prepare("UPDATE invite_codes SET is_used = 1, used_at = NOW() WHERE id = ?");
                $updateStmt->bind_param('i', $invite['id']);
                if (!$updateStmt->execute()) {
                    throw new Exception("Failed to mark invite as used");
                }
                $updateStmt->close();
                
                $conn->commit();
                echo json_encode(["message" => "Registration successful", "error" => false]);
                
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(["message" => "Registration failed: " . $e->getMessage(), "error" => true]);
            }
        } elseif ($type === "register_security") {
            $user = $requestData['user'] ?? '';
            $pass = $requestData['pass'] ?? '';

            if (empty($user) || empty($pass)) {
                echo json_encode(["message" => "Username and password required", "error" => true]);
                exit;
            }

            $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO security (user, pass) VALUES (?, ?)");
            $stmt->bind_param('ss', $user, $hashedPass);
            
            if ($stmt->execute()) {
                echo json_encode(["message" => "Insert successful", "error" => false]);
            } else {
                echo json_encode(["message" => "Insert failed: " . $stmt->error, "error" => true]);
            }
            $stmt->close();
        } elseif ($type === "register_admin") {
            $user = $requestData['user'] ?? '';
            $pass = $requestData['pass'] ?? '';
            $access_level = intval($requestData['access_level'] ?? 1);

            // Ensure user is logged in
            if (!isset($_SESSION['id']) || !isset($_SESSION['access_level'])) {
                echo json_encode(["error" => true, "message" => "Unauthorized"]);
                exit;
            }

            // Prevent creating admin with higher level than yourself
            if ($access_level > $_SESSION['access_level']) {
                echo json_encode(["error" => true, "message" => "You cannot assign a higher access level than your own."]);
                exit;
            }

            if (empty($user) || empty($pass)) {
                echo json_encode(["error" => true, "message" => "Username and password required"]);
                exit;
            }

            $hashedPass = password_hash($pass, PASSWORD_BCRYPT);

            $stmt = $conn->prepare("INSERT INTO admins (user, pass, access_level) VALUES (?, ?, ?)");
            $stmt->bind_param('ssi', $user, $hashedPass, $access_level);
            
            if ($stmt->execute()) {
                echo json_encode(["message" => "Insert successful", "error" => false]);
            } else {
                echo json_encode(["message" => "Insert failed: " . $stmt->error, "error" => true]);
            }
            $stmt->close();
        } elseif ($type === "resident") {
            $created_by = $requestData['created_by'] ?? '';
            // Validate created_by is numeric
            if ($created_by === '' || !ctype_digit(strval($created_by))) {
                echo json_encode(["message" => "Invalid resident ID for QR creation: $created_by", "error" => true]);
                exit;
            }
            $name = $requestData['name'] ?? '';
            $plate = $requestData['plate'] ?? '';
            $expiry = $requestData['expiry'] ?? '';
            $email = $requestData['email'] ?? '';
            
            // Check if car plate is blacklisted
            $blacklistCheck = $conn->prepare("SELECT id FROM blacklist WHERE blacklisted_car_plate = ?");
            $blacklistCheck->bind_param('s', $plate);
            $blacklistCheck->execute();
            $blacklistResult = $blacklistCheck->get_result();
            
            if ($blacklistResult->num_rows > 0) {
                echo json_encode(["message" => "Cannot create QR code: Car plate '$plate' is blacklisted", "error" => true]);
                exit;
            }
            
            $token = bin2hex(random_bytes(5));
            $is_blocked = $requestData['is_blocked'] ?? 0;
            
            // Insert into database first using prepared statement
            $stmt = $conn->prepare("INSERT INTO codes (token, created_by, expiry, intended_visitor, plate_id, is_blocked) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sisssi', $token, $created_by, $expiry, $name, $plate, $is_blocked);
            
            if (!$stmt->execute()) {
                echo json_encode(["message" => "Database insert failed: " . $stmt->error, "error" => true]);
                $stmt->close();
                exit;
            }
            $stmt->close();
            
            // Generate QR code image
            $qr_path = "qr/" . $token . ".png";
            if (!file_exists("qr")) {
                mkdir("qr", 0755, true);
            }
            
            // Generate QR code with token as content
            QRcode::png($token, $qr_path, 'L', 4, 2);
            
            // Get the inserted ID
            $insert_id = $conn->insert_id;
            
            // Send email if provided
            $email_sent = false;
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Get resident information for email
                $resident_stmt = $conn->prepare("SELECT user, room_code FROM residents WHERE id = ?");
                $resident_stmt->bind_param('i', $created_by);
                $resident_stmt->execute();
                $resident_result = $resident_stmt->get_result();
                if ($resident_result && $resident_result->num_rows > 0) {
                    $resident = $resident_result->fetch_assoc();
                    $email_sent = sendQREmail($email, $token, $name, $plate, $expiry, $resident['user'], $resident['room_code']);
                }
                $resident_stmt->close();
            }
            
            $response = [
                "message" => "QR code created successfully", 
                "error" => false, 
                "id" => $insert_id, 
                "token" => $token
            ];
            
            if (!empty($email)) {
                $response["email_sent"] = $email_sent;
                if ($email_sent) {
                    $response["message"] = "QR code created and email sent successfully";
                } else {
                    $response["message"] = "QR code created successfully, but email could not be sent";
                }
            }
            
            echo json_encode($response);
        } elseif ($type === "guest") {
            $token = $requestData['token'] ?? '';
            $scan_by = $requestData['scan_by'] ?? '';

            if (empty($token) || empty($scan_by)) {
                echo json_encode(["message" => "Token and scan_by are required", "error" => true]);
                exit;
            }

            // Validate scan_by is a valid security user ID
            if (!is_numeric($scan_by) || $scan_by <= 0) {
                echo json_encode(["message" => "Invalid security user ID. Please log in again.", "error" => true]);
                exit;
            }

            // Check if security user exists
            $stmt = $conn->prepare("SELECT id FROM security WHERE id = ?");
            $stmt->bind_param('i', $scan_by);
            $stmt->execute();
            $security_check = $stmt->get_result();
            if (!$security_check || $security_check->num_rows === 0) {
                $stmt->close();
                echo json_encode(["message" => "Security user not found. Please log in again.", "error" => true]);
                exit;
            }
            $stmt->close();

            // Check if token exists and is still valid
            $stmt = $conn->prepare("SELECT c.*, r.user AS resident_name, r.room_code 
                    FROM codes c 
                    LEFT JOIN residents r ON c.created_by = r.id 
                    WHERE c.token = ? AND c.expiry > NOW()");
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // Check if this token has already been logged as 'In'
                $logStmt = $conn->prepare("SELECT * FROM logs WHERE token = ? ORDER BY scan_time DESC LIMIT 1");
                $logStmt->bind_param('s', $token);
                $logStmt->execute();
                $logCheck = $logStmt->get_result();

                if ($logCheck && $logCheck->num_rows > 0) {
                    $lastLog = $logCheck->fetch_assoc();

                    if ($lastLog['scan_type'] === 'In') {
                        // Check if QR code is exit-blocked
                        if ($row['is_blocked'] == 1) {
                            // Create notification for the resident about blocked exit attempt
                            $notification_message = "Your visitor '{$row['intended_visitor']}' attempted to exit but was blocked. Please unblock the QR code if you want to allow them to leave.";
                            $notifStmt = $conn->prepare("INSERT INTO notifications (resident_id, message) VALUES (?, ?)");
                            $notifStmt->bind_param('is', $row['created_by'], $notification_message);
                            $notifStmt->execute();
                            $notifStmt->close();

                            echo json_encode([
                                "message" => "Exit denied: This QR code is exit-blocked. Please contact the resident to unblock it.",
                                "error" => true,
                                "visitor" => $row['intended_visitor'],
                                "plate" => $row['plate_id'],
                                "resident" => $row['resident_name'],
                                "room" => $row['room_code'],
                                "status" => "Exit Blocked"
                            ]);
                            exit;
                        }
                        
                        // If last was 'In', this scan should be 'Out' → expire the code
                        $logStmt2 = $conn->prepare("INSERT INTO logs (token, scan_type, scan_by) VALUES (?, 'Out', ?)");
                        $logStmt2->bind_param('si', $token, $scan_by);
                        $logStmt2->execute();
                        $logStmt2->close();

                        // Create notification for the resident when visitor checks out
                        $notification_message = "Your visitor '{$row['intended_visitor']}' has checked out and left.";
                        $notifStmt2 = $conn->prepare("INSERT INTO notifications (resident_id, message) VALUES (?, ?)");
                        $notifStmt2->bind_param('is', $row['created_by'], $notification_message);
                        $notifStmt2->execute();
                        $notifStmt2->close();

                        // Expire the code immediately
                        $expireStmt = $conn->prepare("UPDATE codes SET expiry = NOW() WHERE token = ?");
                        $expireStmt->bind_param('s', $token);
                        $expireStmt->execute();
                        $expireStmt->close();

                        echo json_encode([
                            "message" => "Visitor {$row['intended_visitor']} checked OUT successfully.",
                            "error" => false,
                            "visitor" => $row['intended_visitor'],
                            "plate" => $row['plate_id'],
                            "resident" => $row['resident_name'],
                            "room" => $row['room_code'],
                            "status" => "Out"
                        ]);
                        exit;
                    }
                }

                // Default → first scan, mark as 'In'
                $logStmt3 = $conn->prepare("INSERT INTO logs (token, scan_type, scan_by) VALUES (?, 'In', ?)");
                $logStmt3->bind_param('si', $token, $scan_by);
                $logStmt3->execute();
                $logStmt3->close();

                // Create notification for the resident who created the QR code
                $notification_message = "Your visitor '{$row['intended_visitor']}' has arrived and checked in.";
                $notifStmt3 = $conn->prepare("INSERT INTO notifications (resident_id, message) VALUES (?, ?)");
                $notifStmt3->bind_param('is', $row['created_by'], $notification_message);
                $notifStmt3->execute();
                $notifStmt3->close();

                echo json_encode([
                    "message" => "Welcome {$row['intended_visitor']}! Entry logged successfully.",
                    "error" => false,
                    "visitor" => $row['intended_visitor'],
                    "plate" => $row['plate_id'],
                    "resident" => $row['resident_name'],
                    "room" => $row['room_code'],
                    "status" => "In"
                ]);
            } else {
                echo json_encode([
                    "message" => "Invalid or expired QR code. Please contact the resident for a new invitation.",
                    "error" => true
                ]);
            }

        } elseif ($type === "chat") {
            $sender_id = $requestData['sender_id'] ?? '';
            $sender_type = $requestData['sender_type'] ?? '';
            $receiver_id = $requestData['receiver_id'] ?? '';
            $receiver_type = $requestData['receiver_type'] ?? '';
            $message = $requestData['message'] ?? '';
            
            // Validate input
            if (empty($sender_id) || empty($sender_type) || empty($receiver_id) || empty($receiver_type) || empty($message)) {
                echo json_encode(["message" => "All fields are required", "error" => true]);
                exit;
            }
            
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, sender_type, receiver_id, receiver_type, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('isiss', $sender_id, $sender_type, $receiver_id, $receiver_type, $message);
            
            if ($stmt->execute()) {
                echo json_encode(["message" => "Insert successful", "error" => false]);
            } else {
                echo json_encode(["message" => "Insert failed: " . $stmt->error, "error" => true]);
            }
            $stmt->close();
        } elseif ($type === "create_announcement") {
            $title = $requestData['title'] ?? '';
            $content = $requestData['content'] ?? '';

            if (empty($title) || empty($content)) {
                echo json_encode(["message" => "Title and content are required", "error" => true]);
                exit;
            }

            // Check what columns exist in announcements table
            $columns_check = $conn->query("SHOW COLUMNS FROM announcements");
            $available_columns = [];
            while ($col = $columns_check->fetch_assoc()) {
                $available_columns[] = $col['Field'];
            }
            
            // Build INSERT query based on available columns
            if (in_array('created_by', $available_columns)) {
                $created_by = $_SESSION['id'] ?? 0;
                $stmt = $conn->prepare("INSERT INTO announcements (title, content, post_time, created_by) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('sssi', $title, $content, $time, $created_by);
            } else {
                // If created_by doesn't exist, just insert title, content, and post_time
                $stmt = $conn->prepare("INSERT INTO announcements (title, content, post_time) VALUES (?, ?, ?)");
                $stmt->bind_param('sss', $title, $content, $time);
            }
            
            if ($stmt->execute()) {
                echo json_encode(["message" => "Insert successful", "error" => false]);
            } else {
                echo json_encode(["message" => "Insert failed: " . $stmt->error, "error" => true]);
            }
            $stmt->close();
        }

        break;
    case "PUT":
        $type = $requestData['type'] ?? '';
        
        // CSRF Protection for PUT operations
        $csrf_token = $requestData['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            http_response_code(403);
            echo json_encode(["message" => "CSRF token validation failed", "error" => true]);
            exit;
        }
        
        if ($type === "resident") {
            if (isset($requestData['id']) && !isset($requestData['name'])) {
                $id = $requestData['id'];
                if (!is_numeric($id)) {
                    echo json_encode(["message" => "Invalid notification ID", "error" => true]);
                    exit;
                }
                $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
                $stmt->bind_param('i', $id);
                if ($stmt->execute()) {
                    echo json_encode(["message" => "Update successful", "error" => false]);
                } else {
                    echo json_encode(["message" => "Update failed: " . $stmt->error, "error" => true]);
                }
                $stmt->close();
            } else {
                $id = $requestData['id'] ?? '';
                $name = $requestData['name'] ?? '';
                $plate = $requestData['plate'] ?? '';
                $expiry = $requestData['expiry'] ?? '';
                $is_blocked = $requestData['is_blocked'] ?? 0;
                
                if (!is_numeric($id)) {
                    echo json_encode(["message" => "Invalid code ID", "error" => true]);
                    exit;
                }
                
                $stmt = $conn->prepare("UPDATE codes SET expiry=?, intended_visitor=?, plate_id=?, is_blocked=? WHERE id = ?");
                $stmt->bind_param('sssii', $expiry, $name, $plate, $is_blocked, $id);
                
                if (!$stmt->execute()) {
                    echo json_encode(["message" => "Update failed: " . $stmt->error, "error" => true]);
                    $stmt->close();
                    exit;
                }
                $stmt->close();
                
                // Get the token for this code to regenerate QR
                $tokenStmt = $conn->prepare("SELECT token FROM codes WHERE id = ?");
                $tokenStmt->bind_param('i', $id);
                $tokenStmt->execute();
                $token_result = $tokenStmt->get_result();
                if ($token_result && $token_result->num_rows > 0) {
                    $row = $token_result->fetch_assoc();
                    $token = $row['token'];
                    
                    // Regenerate QR code image
                    $qr_path = "qr/" . $token . ".png";
                    if (!file_exists("qr")) {
                        mkdir("qr", 0755, true);
                    }
                    
                    // Generate QR code with token as content
                    QRcode::png($token, $qr_path, 'L', 4, 2);
                }
                $tokenStmt->close();
                
                echo json_encode(["message" => "Update successful", "error" => false]);
            }
        } elseif ($type === "toggle_exit_block") {
            $id = $requestData['id'] ?? '';
            $is_blocked = $requestData['is_blocked'] ?? 0;
            
            if (!is_numeric($id)) {
                echo json_encode(["message" => "Invalid code ID", "error" => true]);
                exit;
            }
            
            $stmt = $conn->prepare("UPDATE codes SET is_blocked = ? WHERE id = ?");
            $stmt->bind_param('ii', $is_blocked, $id);
            if ($stmt->execute()) {
                echo json_encode(["message" => "Update successful", "error" => false]);
            } else {
                echo json_encode(["message" => "Update failed: " . $stmt->error, "error" => true]);
            }
            $stmt->close();
        } elseif ($type === "update_resident") {
            if (isset($requestData['id']) && !isset($requestData['pass'])) {
                $id = $requestData['id'];
                $user = $requestData['user'] ?? '';
                $room_code = $requestData['room_code'] ?? '';
                
                if (!is_numeric($id) || empty($user) || empty($room_code)) {
                    echo json_encode(["message" => "Invalid data provided for resident update", "error" => true]);
                    exit;
                }
                
                $stmt = $conn->prepare("UPDATE residents SET user=?, room_code=? WHERE id = ?");
                $stmt->bind_param('ssi', $user, $room_code, $id);
                if ($stmt->execute()) {
                    echo json_encode(["message" => "Update successful", "error" => false]);
                } else {
                    echo json_encode(["message" => "Update failed: " . $stmt->error, "error" => true]);
                }
                $stmt->close();
            }
        } elseif ($type === "update_admin") {
            if (isset($requestData['id'])) {
                $id = $requestData['id'];
                $user = $requestData['user'] ?? '';
                $access_level = $requestData['access_level'] ?? 1;

                // Optional validation
                if (!is_numeric($id) || empty($user) || !ctype_digit(strval($access_level))) {
                    echo json_encode(["message" => "Invalid data provided for admin update", "error" => true]);
                    exit;
                }

                $stmt = $conn->prepare("UPDATE admins SET user=?, access_level=? WHERE id = ?");
                $stmt->bind_param('sii', $user, $access_level, $id);
                if ($stmt->execute()) {
                    echo json_encode(["message" => "Update successful", "error" => false]);
                } else {
                    echo json_encode(["message" => "Update failed: " . $stmt->error, "error" => true]);
                }
                $stmt->close();
            }
        } elseif ($type === "update_security") {
            if (isset($requestData['id'])) {
                $id = $requestData['id'];
                $user = $requestData['user'] ?? '';
                
                if (!is_numeric($id) || empty($user)) {
                    echo json_encode(["message" => "Invalid data provided for security update", "error" => true]);
                    exit;
                }
                
                $stmt = $conn->prepare("UPDATE security SET user=? WHERE id=?");
                $stmt->bind_param('si', $user, $id);
                if ($stmt->execute()) {
                    echo json_encode(["message" => "Update successful", "error" => false]);
                } else {
                    echo json_encode(["message" => "Update failed: " . $stmt->error, "error" => true]);
                }
                $stmt->close();
            }
        } elseif ($type === "edit_announcement") {
            $id = $requestData['id'] ?? '';
            $title = $requestData['title'] ?? '';
            $content = $requestData['content'] ?? '';

            if (empty($id) || empty($title) || empty($content) || !is_numeric($id)) {
                echo json_encode(["message" => "Valid ID, title, and content are required", "error" => true]);
                exit;
            }

            $stmt = $conn->prepare("UPDATE announcements SET title=?, content=?, post_time=? WHERE id=?");
            $stmt->bind_param('sssi', $title, $content, $time, $id);
            if ($stmt->execute()) {
                echo json_encode(["message" => "Update successful", "error" => false]);
            } else {
                echo json_encode(["message" => "Update failed: " . $stmt->error, "error" => true]);
            }
            $stmt->close();
        }

        break;
    case "DELETE":
        $type = $requestData['type'] ?? '';
        $id   = $requestData['id'] ?? '';
        $fetch = $requestData['fetch'] ?? '';
        
        // CSRF Protection for DELETE operations
        $csrf_token = $requestData['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            http_response_code(403);
            echo json_encode(["message" => "CSRF token validation failed", "error" => true]);
            exit;
        }

        if ($type === "resident") {
            if (!is_numeric($id)) {
                echo json_encode(["message" => "Invalid code ID", "error" => true]);
                exit;
            }
            $stmt = $conn->prepare("DELETE FROM codes WHERE id = ?");
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                echo json_encode(["message" => "Delete successful", "error" => false]);
            } else {
                echo json_encode(["message" => "Delete failed: " . $stmt->error, "error" => true]);
            }
            $stmt->close();
        } elseif ($type === "admin") {
            if (!is_numeric($id)) {
                echo json_encode(["message" => "Invalid ID", "error" => true]);
                exit;
            }
            
            if ($fetch === 'admin') {
                // Prevent deleting your own account
                if ($id == $_SESSION['id']) {
                    echo json_encode([
                        "error" => true,
                        "message" => "You cannot delete your own account."
                    ]);
                    break;
                }
                $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
            } elseif ($fetch === 'resident') {
                $stmt = $conn->prepare("DELETE FROM residents WHERE id = ?");
            } elseif ($fetch === 'security') {
                $stmt = $conn->prepare("DELETE FROM security WHERE id = ?");
            } elseif ($fetch === 'announcement') {
                $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
            } elseif ($fetch === 'invite_code') {
                $stmt = $conn->prepare("DELETE FROM invite_codes WHERE id = ?");
            } elseif ($fetch === 'blacklist') {
                $stmt = $conn->prepare("DELETE FROM blacklist WHERE id = ?");
            } else {
                echo json_encode(["message" => "Invalid fetch type", "error" => true]);
                exit;
            }
            
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                echo json_encode(["message" => "Delete successful", "error" => false]);
            } else {
                echo json_encode(["message" => "Delete failed: " . $stmt->error, "error" => true]);
            }
            $stmt->close();
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed", "error" => true]);
        break;
}
?>