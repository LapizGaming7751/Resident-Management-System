<?php
// Unified API for Finals_CheckInSystem
// Fixed version with proper announcement CRUD operations

date_default_timezone_set("Singapore");
include("phpqrcode/qrlib.php");

// Disable error display for API responses to prevent HTML in JSON
error_reporting(E_ALL);
ini_set('display_errors', 0); // Changed to 0 to prevent HTML errors in JSON response
ini_set('log_errors', 1); // Log errors instead of displaying them

$host = 'localhost';
$db = 'synergy1_siewyaoying_resident_management';
$db_user = 'synergy1';
$db_pass = 'Hu49xW-b[8lY0R';

$conn = new mysqli($host, $db_user, $db_pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed: " . $conn->connect_error]);
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

session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: " . ($_SERVER['HTTP_ORIGIN'] ?? 'https://siewyaoying.synergy-college.org'));
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
function fetchList($conn, $sql) {
    $result = $conn->query($sql);
    $list = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $list[] = $row;
        }
        return $list;
    } else {
        error_log('SQL Error: ' . $conn->error . ' | Query: ' . $sql);
        http_response_code(500);
        echo json_encode(["message" => "Database error: " . $conn->error, "error" => true, "sql" => $sql]);
        exit;
    }
}

function fetchMessages($conn, $sender_id, $sender_type, $receiver_id, $receiver_type) {
    $sql = "SELECT * FROM messages WHERE ((sender_id = '$sender_id' AND sender_type = '$sender_type' AND receiver_id = '$receiver_id' AND receiver_type = '$receiver_type') OR (sender_id = '$receiver_id' AND sender_type = '$receiver_type' AND receiver_id = '$sender_id' AND receiver_type = '$sender_type')) ORDER BY created_at ASC";
    return fetchList($conn, $sql);
}

function login($conn, $table, $user, $pass, $session_keys) {
    $sql = "SELECT * FROM $table WHERE user = '$user'";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        if ($row && password_verify($pass, $row['pass'])) {
            foreach ($session_keys as $key => $val) {
                $_SESSION[$key] = $row[$val];
            }
            $_SESSION['type'] = $table;
            return ["message" => "Welcome, " . $row['user'] . ".", "error" => false];
        }
        return ["message" => ucfirst($table) . " not found", "error" => true];
    }
    return ["message" => "Query failed: " . $conn->error, "error" => true];
}

function errorResponse($msg) {
    http_response_code(500);
    echo json_encode(["message" => $msg, "error" => true]);
    exit;
}

function insertRow($conn, $sql) {
    if ($conn->query($sql)) {
        return ["message" => "Insert successful", "error" => false];
    }
    return ["message" => "Insert failed: " . $conn->error, "error" => true];
}

function updateRow($conn, $sql) {
    if ($conn->query($sql)) {
        return ["message" => "Update successful", "error" => false];
    }
    return ["message" => "Update failed: " . $conn->error, "error" => true];
}

function deleteRow($conn, $sql) {
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
        // Validate resident/guard ID is numeric
        if ($id !== '' && !ctype_digit(strval($id))) {
            errorResponse("Invalid resident/guard ID: $id");
        }
        if ($type === "resident") {
            if ($fetch === "announcements") {
                $list = fetchList($conn, "SELECT * FROM announcements ORDER BY post_time DESC");
                echo json_encode($list !== false ? $list : []);
                exit;
            }elseif ($id && ($fetch === '' || $fetch === 'qr' || !isset($_GET['fetch']))) {
                // QR fetch: ?type=resident&created_by=ID or ?type=resident&created_by=ID&fetch=qr
                $list = fetchList($conn, "SELECT * FROM codes WHERE created_by = '$id'");
                echo json_encode($list !== false ? $list : []);
            } else if ($id && $fetch) {
                switch ($fetch) {
                    case "notifications":
                        $list = fetchList($conn, "SELECT * FROM notifications WHERE resident_id = '$id' AND is_read = 0 ORDER BY created_at DESC");
                        echo json_encode($list !== false ? $list : []);
                        break;
                    case "messages":
                        $security_id = $_GET['security_id'] ?? 0;
                        $list = fetchMessages($conn, $id, 'resident', $security_id, 'security');
                        echo json_encode($list !== false ? $list : []);
                        break;
                    case "security_list":
                        $list = fetchList($conn, "SELECT id, user FROM security");
                        echo json_encode($list !== false ? $list : []);
                        break;
                    case "messaged_security_list":
                        $list = fetchList($conn, "SELECT DISTINCT s.id, s.user FROM messages m INNER JOIN security s ON m.sender_id = s.id WHERE m.receiver_id = '$id' AND m.receiver_type = 'resident' AND m.sender_type = 'security' ORDER BY m.id DESC");
                        echo json_encode($list !== false ? $list : []);
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
                        $list = fetchList($conn, "SELECT * FROM residents");
                        echo json_encode($list !== false ? $list : []);
                        break;
                    case "admin":
                        $list = fetchList($conn, "SELECT * FROM admins");
                        echo json_encode($list !== false ? $list : []);
                        break;
                    case "log":
                        $list = fetchList($conn, "SELECT logs.id, logs.token, logs.scan_time, logs.scan_type, logs.scan_by, security.user AS scanner_username, codes.intended_visitor AS intended_visitor, residents.user AS created_by_username, residents.room_code AS created_by_room FROM logs LEFT JOIN security ON logs.scan_by = security.id LEFT JOIN codes ON logs.token = codes.token LEFT JOIN residents ON codes.created_by = residents.id");
                        echo json_encode($list !== false ? $list : []);
                        break;
                    case "security":
                        $list = fetchList($conn, "SELECT * FROM security");
                        echo json_encode($list !== false ? $list : []);
                        break;
                    case "announcements":
                        $list = fetchList($conn, "SELECT * FROM announcements ORDER BY post_time DESC");
                        echo json_encode($list !== false ? $list : []);
                        break;
                    case "invite_codes":
                        $list = fetchList($conn, "SELECT ic.*, a.user as created_by_name FROM invite_codes ic LEFT JOIN admins a ON ic.created_by = a.id ORDER BY ic.created_at DESC");
                        echo json_encode($list !== false ? $list : []);
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
                        $list = fetchList($conn, "SELECT id, user, room_code FROM residents");
                        echo json_encode($list !== false ? $list : []);
                        break;
                    default:
                        errorResponse("Fetch not recognized");
                }
            } else if (isset($_GET['user']) && isset($_GET['pass'])) {
                $resp = login($conn, 'security', $_GET['user'], $_GET['pass'], ["id" => "id", "user" => "user"]);
                echo json_encode($resp);
            }
        }
        break;
    case "POST":
        $type = $requestData['type'] ?? '';
        
        // Handle announcement deletion (using POST with action parameter)
        if ($type === "admin" && isset($requestData['fetch']) && $requestData['fetch'] === 'announcement' && isset($requestData['action']) && $requestData['action'] === 'delete') {
            $id = $requestData['id'] ?? '';
            if (empty($id)) {
                echo json_encode(["message" => "Announcement ID is required", "error" => true]);
                exit;
            }
            $sql = "DELETE FROM announcements WHERE id = '$id'";
            echo json_encode(deleteRow($conn, $sql));
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
            $table = $user_type === 'admin' ? 'admins' : $user_type . 's';
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
            $conn->query("UPDATE password_reset_tokens SET is_used = 1 WHERE user_id = $user_id AND user_type = '$user_type'");
            
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
            $table = $resetToken['user_type'] === 'admin' ? 'admins' : $resetToken['user_type'] . 's';
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
            $table = $invite['user_type'] === 'resident' ? 'residents' : 'security';
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
            $pass = password_hash($requestData['pass'] ?? '', PASSWORD_DEFAULT);

            if (empty($user) || empty($requestData['pass'])) {
                echo json_encode(["message" => "Username and password required", "error" => true]);
                exit;
            }

            $sql = "INSERT INTO security (user, pass) VALUES ('$user', '$pass')";
            echo json_encode(insertRow($conn, $sql));
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

            $sql = "INSERT INTO admins (user, pass, access_level) 
                    VALUES ('$user', '$hashedPass', '$access_level')";
            echo json_encode(insertRow($conn, $sql));
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
            $token = bin2hex(random_bytes(5));
            $sql = "INSERT INTO codes (token, created_by, expiry, intended_visitor, plate_id) VALUES ('$token','$created_by','$expiry','$name','$plate')";
            
            // Insert into database first
            $result = $conn->query($sql);
            if (!$result) {
                echo json_encode(["message" => "Database insert failed: " . $conn->error, "error" => true]);
                exit;
            }
            
            // Generate QR code image
            $qr_path = "qr/" . $token . ".png";
            if (!file_exists("qr")) {
                mkdir("qr", 0777, true);
            }
            
            // Generate QR code with token as content
            QRcode::png($token, $qr_path, 'L', 4, 2);
            
            // Get the inserted ID
            $insert_id = $conn->insert_id;
            
            // Send email if provided
            $email_sent = false;
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Get resident information for email
                $resident_query = "SELECT user, room_code FROM residents WHERE id = '$created_by'";
                $resident_result = $conn->query($resident_query);
                if ($resident_result && $resident_result->num_rows > 0) {
                    $resident = $resident_result->fetch_assoc();
                    $email_sent = sendQREmail($email, $token, $name, $plate, $expiry, $resident['user'], $resident['room_code']);
                }
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

            // Check if token exists and is still valid
            $sql = "SELECT c.*, r.user AS resident_name, r.room_code 
                    FROM codes c 
                    LEFT JOIN residents r ON c.created_by = r.id 
                    WHERE c.token = '$token' AND c.expiry > NOW()";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // Check if this token has already been logged as 'In'
                $logCheck = $conn->query("SELECT * FROM logs WHERE token = '$token' ORDER BY scan_time DESC LIMIT 1");

                if ($logCheck && $logCheck->num_rows > 0) {
                    $lastLog = $logCheck->fetch_assoc();

                    if ($lastLog['scan_type'] === 'In') {
                        // If last was 'In', this scan should be 'Out' → expire the code
                        $log_sql = "INSERT INTO logs (token, scan_type, scan_by) 
                                    VALUES ('$token', 'Out', '$scan_by')";
                        $conn->query($log_sql);

                        // Expire the code immediately
                        $conn->query("UPDATE codes SET expiry = NOW() WHERE token = '$token'");

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
                $log_sql = "INSERT INTO logs (token, scan_type, scan_by) 
                            VALUES ('$token', 'In', '$scan_by')";
                $conn->query($log_sql);

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
            $message = $conn->real_escape_string($requestData['message'] ?? '');
            $sql = "INSERT INTO messages (sender_id, sender_type, receiver_id, receiver_type, message) VALUES ('$sender_id', '$sender_type', '$receiver_id', '$receiver_type', '$message')";
            echo json_encode(insertRow($conn, $sql));
        } elseif ($type === "create_announcement") {
            $title = $conn->real_escape_string($requestData['title'] ?? '');
            $content = $conn->real_escape_string($requestData['content'] ?? '');

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
                $sql = "INSERT INTO announcements (title, content, post_time, created_by) 
                        VALUES ('$title', '$content', '$time', '$created_by')";
            } else {
                // If created_by doesn't exist, just insert title, content, and post_time
                $sql = "INSERT INTO announcements (title, content, post_time) 
                        VALUES ('$title', '$content', '$time')";
            }
            
            echo json_encode(insertRow($conn, $sql));
        }

        break;
    case "PUT":
        $type = $requestData['type'] ?? '';
        if ($type === "resident") {
            if (isset($requestData['id']) && !isset($requestData['name'])) {
                $id = $requestData['id'];
                $sql = "UPDATE notifications SET is_read = 1 WHERE id = '$id'";
                echo json_encode(updateRow($conn, $sql));
            } else {
                $id = $requestData['id'] ?? '';
                $name = $requestData['name'] ?? '';
                $plate = $requestData['plate'] ?? '';
                $expiry = $requestData['expiry'] ?? '';
                $sql = "UPDATE codes SET expiry='$expiry', intended_visitor='$name', plate_id='$plate' WHERE id = '$id'";
                
                $result = $conn->query($sql);
                if (!$result) {
                    echo json_encode(["message" => "Update failed: " . $conn->error, "error" => true]);
                    exit;
                }
                
                // Get the token for this code to regenerate QR
                $token_sql = "SELECT token FROM codes WHERE id = '$id'";
                $token_result = $conn->query($token_sql);
                if ($token_result && $token_result->num_rows > 0) {
                    $row = $token_result->fetch_assoc();
                    $token = $row['token'];
                    
                    // Regenerate QR code image
                    $qr_path = "qr/" . $token . ".png";
                    if (!file_exists("qr")) {
                        mkdir("qr", 0777, true);
                    }
                    
                    // Generate QR code with token as content
                    QRcode::png($token, $qr_path, 'L', 4, 2);
                }
                
                echo json_encode(["message" => "Update successful", "error" => false]);
            }
        } elseif ($type === "update_resident") {
            if (isset($requestData['id']) && !isset($requestData['pass'])) {
                $id = $requestData['id'];
                $user = $requestData['user'] ?? '';
                $room_code = $requestData['room_code'] ?? '';
                // ...validation logic...
                $sql = "UPDATE residents SET user='$user', room_code='$room_code' WHERE id = '$id'";
                echo json_encode(updateRow($conn, $sql));
            }
        } elseif ($type === "update_admin") {
            if (isset($requestData['id'])) {
                $id = $requestData['id'];
                $user = $requestData['user'] ?? '';
                $access_level = $requestData['access_level'] ?? 1;

                // Optional validation
                if (empty($user) || !ctype_digit(strval($access_level))) {
                    echo json_encode(["message" => "Invalid data provided for admin update", "error" => true]);
                    exit;
                }

                $sql = "UPDATE admins SET user='$user', access_level='$access_level' WHERE id = '$id'";
                echo json_encode(updateRow($conn, $sql));
            }
        } elseif ($type === "update_security") {
            if (isset($requestData['id'])) {
                $id = $requestData['id'];
                $user = $requestData['user'] ?? '';
                $sql = "UPDATE security SET user='$user' WHERE id='$id'";
                echo json_encode(updateRow($conn, $sql));
            }
        } elseif ($type === "edit_announcement") {
            $id = $requestData['id'] ?? '';
            $title = $conn->real_escape_string($requestData['title'] ?? '');
            $content = $conn->real_escape_string($requestData['content'] ?? '');

            if (empty($id) || empty($title) || empty($content)) {
                echo json_encode(["message" => "ID, title, and content are required", "error" => true]);
                exit;
            }

            $sql = "UPDATE announcements SET title='$title', content='$content', post_time='$time' WHERE id='$id'";
            echo json_encode(updateRow($conn, $sql));
        }

        break;
    case "DELETE":
        $type = $requestData['type'] ?? '';
        $id   = $requestData['id'] ?? '';
        $fetch = $requestData['fetch'] ?? '';

        if ($type === "resident") {
            $sql = "DELETE FROM codes WHERE id = '$id'";
            echo json_encode(deleteRow($conn, $sql));
        } elseif ($type === "admin") {
            if ($fetch === 'admin') {
                // Prevent deleting your own account
                if ($id == $_SESSION['id']) {
                    echo json_encode([
                        "error" => true,
                        "message" => "You cannot delete your own account."
                    ]);
                    break;
                }
                $sql = "DELETE FROM admins WHERE id = '$id'";
            } elseif ($fetch === 'resident') {
                $sql = "DELETE FROM residents WHERE id = '$id'";
            } elseif ($fetch === 'security') {
                $sql = "DELETE FROM security WHERE id = '$id'";
            } elseif ($fetch === 'announcement') {
                $sql = "DELETE FROM announcements WHERE id = '$id'";
            } elseif ($fetch === 'invite_code') {
                $sql = "DELETE FROM invite_codes WHERE id = '$id'";
            }
            echo json_encode(deleteRow($conn, $sql));
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed", "error" => true]);
        break;
}
?>