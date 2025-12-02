<?php
// Include configuration file for environment variables
require_once("config.php");

include("phpqrcode/qrlib.php");

// Use database connection function from config.php
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed: " . $e->getMessage()]);
    exit;
}

session_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'OPTIONS') {
    http_response_code(200);
    exit;
}

$requestData = json_decode(file_get_contents("php://input"), true);
error_log("Request Data: " . json_encode($requestData));

$time = date("Y-m-d H:i:s");

switch ($method) {

case "GET":
        $type = $_GET['type'] ?? '';
        
        // Handle CSRF token request
        if ($type === 'get_csrf_token') {
            $csrf_token = generateCSRFToken();
            echo json_encode(['csrf_token' => $csrf_token]);
            exit;
        }
        
        if ($type === "resident") {
            if (isset($_GET['fetch']) && $_GET['fetch'] == 'announcements') {
                // Fetch announcements for residents
                $sql = "SELECT id, title, content, post_time FROM announcements ORDER BY post_time DESC";
                $result = $conn->query($sql);
                $announcements = [];
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $announcements[] = $row;
                    }
                    echo json_encode($announcements);
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Error fetching announcements: " . $conn->error, "error" => true]);
                }
            } else if (isset($_GET['created_by'])) {
                $id = $_GET['created_by'];
                // Return QR codes created by this resident
                if (isset($_GET['fetch']) && $_GET['fetch'] == 'qr') {
                    $stmt = $conn->prepare("SELECT id, token, expiry, intended_visitor, plate_id, is_blocked FROM codes WHERE created_by = ? ORDER BY id DESC");
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        $result = $stmt->get_result();
                        $codes = [];
                        while ($row = $result->fetch_assoc()) {
                            $codes[] = $row;
                        }
                        echo json_encode($codes);
                    } else {
                        http_response_code(500);
                        echo json_encode(["message" => "Error fetching QR codes: " . $stmt->error, "error" => true]);
                    }
                    $stmt->close();
                } else if (isset($_GET['fetch']) && $_GET['fetch'] == 'notifications') {
                    // ...existing code...
                } else {
                    // ...existing code...
                }
            } else {
                // ...existing code...
            }

        } elseif ($type === "admin") {
            if (isset($_GET['fetch']) && $_GET['fetch'] == 'announcements') {
                // Fetch announcements for admins
                $sql = "SELECT id, title, content, post_time FROM announcements ORDER BY post_time DESC";
                $result = $conn->query($sql);
                $announcements = [];
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $announcements[] = $row;
                    }
                    echo json_encode($announcements);
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Error fetching announcements: " . $conn->error, "error" => true]);
                }
            } else if (isset($_GET['fetch'])) {
                switch ($_GET['fetch']) {
                    case "resident":
                        // Fetch all residents
                        $sql = "SELECT id, user, room_code, email, is_active FROM residents ORDER BY id DESC";
                        $result = $conn->query($sql);
                        $residents = [];
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                $residents[] = $row;
                            }
                            echo json_encode($residents);
                        } else {
                            http_response_code(500);
                            echo json_encode(["message" => "Error fetching residents: " . $conn->error, "error" => true]);
                        }
                        break;
                    case "admin":
                        // Fetch all admins
                        $sql = "SELECT id, user, email, access_level FROM admins ORDER BY id DESC";
                        $result = $conn->query($sql);
                        $admins = [];
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                $admins[] = $row;
                            }
                            echo json_encode($admins);
                        } else {
                            http_response_code(500);
                            echo json_encode(["message" => "Error fetching admins: " . $conn->error, "error" => true]);
                        }
                        break;
                    case "log":
                        // Fetch logs with proper JOINs to get all needed information
                        $sql = "SELECT 
                                    l.id,
                                    l.token,
                                    l.scan_time,
                                    l.scan_type,
                                    l.scan_by,
                                    c.intended_visitor,
                                    c.plate_id,
                                    c.created_by,
                                    s.user as scanner_username,
                                    r.user as created_by_username,
                                    r.room_code as created_by_room
                                FROM logs l
                                LEFT JOIN codes c ON l.token = c.token
                                LEFT JOIN security s ON l.scan_by = s.id
                                LEFT JOIN residents r ON c.created_by = r.id
                                ORDER BY l.scan_time DESC";
                        $result = $conn->query($sql);
                        $logs = [];
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                $logs[] = $row;
                            }
                            echo json_encode($logs);
                        } else {
                            http_response_code(500);
                            echo json_encode(["message" => "Error fetching logs: " . $conn->error, "error" => true]);
                        }
                        break;
                    case "security":
                        // Fetch all security staff
                        $sql = "SELECT id, user, email, is_active FROM security ORDER BY id DESC";
                        $result = $conn->query($sql);
                        $security = [];
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                $security[] = $row;
                            }
                            echo json_encode($security);
                        } else {
                            http_response_code(500);
                            echo json_encode(["message" => "Error fetching security: " . $conn->error, "error" => true]);
                        }
                        break;
                    case "blacklist":
                        // Fetch blacklist entries
                        $sql = "SELECT id, blacklisted_car_plate, reason, created_at, created_by FROM blacklist ORDER BY created_at DESC";
                        $result = $conn->query($sql);
                        $blacklist = [];
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                $blacklist[] = $row;
                            }
                            echo json_encode($blacklist);
                        } else {
                            http_response_code(500);
                            echo json_encode(["message" => "Error fetching blacklist: " . $conn->error, "error" => true]);
                        }
                        break;
                    case "invite_codes":
                        // Fetch invite codes with creator information
                        $sql = "SELECT 
                                    ic.id,
                                    ic.code,
                                    ic.user_type,
                                    ic.email,
                                    ic.room_code,
                                    ic.created_by,
                                    ic.created_at,
                                    ic.expires_at,
                                    ic.is_used,
                                    ic.used_at,
                                    a.user as created_by_name
                                FROM invite_codes ic
                                LEFT JOIN admins a ON ic.created_by = a.id
                                ORDER BY ic.created_at DESC";
                        $result = $conn->query($sql);
                        $invites = [];
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                $invites[] = $row;
                            }
                            echo json_encode($invites);
                        } else {
                            http_response_code(500);
                            echo json_encode(["message" => "Error fetching invite codes: " . $conn->error, "error" => true]);
                        }
                        break;
                    default:
                        http_response_code(400);
                        echo json_encode(["message" => "Invalid fetch type", "error" => true]);
                        break;
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Missing fetch parameter", "error" => true]);
            }

        } elseif ($type === "security") {
            // ...existing code...

            $sql = "SELECT * FROM security WHERE user = ?";
            $result = $conn->prepare($sql);
            $result->bind_param("s", $_GET['user']);
            $result->execute();
            $res = $result->get_result();

            if ($res) {
                $row = $res->fetch_assoc();
                if ($row && password_verify($pass, $row['pass'])) {
                    echo json_encode(["message" => "Welcome, " . $row['user'] . ".","error"=>false]);

                    $_SESSION['id'] = $row['id'];
                    $_SESSION["user"] = $row['user'];
                    $_SESSION['type'] = "security";
                    
                } else {
                    echo json_encode(["message" => "Security not found","error"=>true]);
                }
            } else {
                echo json_encode(["message" => "Query failed: " . $conn->error,"error"=>true]);
            }
        }elseif ($type === "chat"){
            $fetch = $_GET['fetch'] ?? '';
            $securityId = isset($_GET['security_id']) ? (int)$_GET['security_id'] : 0;
            $residentId = isset($_GET['resident_id']) ? (int)$_GET['resident_id'] : 0;

            if ($fetch === 'messages' && $securityId && $residentId) {
                $stmt = $conn->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
                $stmt->bind_param("iiii", $securityId, $residentId, $residentId, $securityId);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    $messages = [];
                    while ($row = $result->fetch_assoc()) {
                        $messages[] = $row;
                    }
                    echo json_encode($messages);
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Error fetching messages: " . $stmt->error, "error" => true]);
                }
                $stmt->close();
            } else if ($fetch === 'chatted_security' && $residentId) {
                // Fetch security guards who have chatted with this resident
                $sql = "SELECT DISTINCT s.id, s.user FROM security s JOIN messages m ON ((m.sender_id = ? AND m.receiver_id = s.id) OR (m.receiver_id = ? AND m.sender_id = s.id))";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $residentId, $residentId);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    $guards = [];
                    while ($row = $result->fetch_assoc()) {
                        $guards[] = $row;
                    }
                    echo json_encode($guards);
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Error fetching chatted security: " . $stmt->error, "error" => true]);
                }
                $stmt->close();
            } else if ($fetch === 'chatted_residents' && $securityId) {
                // Fetch residents who have chatted with this security
                $sql = "SELECT DISTINCT r.id, r.user, r.room_code FROM residents r JOIN messages m ON ((m.sender_id = ? AND m.receiver_id = r.id) OR (m.receiver_id = ? AND m.sender_id = r.id))";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $securityId, $securityId);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    $residents = [];
                    while ($row = $result->fetch_assoc()) {
                        $residents[] = $row;
                    }
                    echo json_encode($residents);
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Error fetching chatted residents: " . $stmt->error, "error" => true]);
                }
                $stmt->close();
            } else {
                echo json_encode(["message" => "Invalid chat request","error"=>true]);
            }
        }elseif ($type === "call"){
            $action = $_GET['action'] ?? '';
            $securityId = isset($_GET['security_id']) ? (int)$_GET['security_id'] : 0;
            $residentId = isset($_GET['resident_id']) ? (int)$_GET['resident_id'] : 0;

            if ($action === 'get_answer' && $securityId && $residentId) {
                $stmt = $conn->prepare("SELECT answer FROM calls WHERE security_id = ? AND resident_id = ? AND status = 'pending'");
                $stmt->bind_param("ii", $securityId, $residentId);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        echo json_encode(['answer' => json_decode($row['answer'])]);
                    } else {
                        echo json_encode(['answer' => null]);
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Error fetching call answer: " . $stmt->error, "error" => true]);
                }
                $stmt->close();
            } else {
                echo json_encode(["message" => "Invalid call request","error"=>true]);
            }
        }else{
            echo json_encode(["message" => "Type not recognized","error"=>true]);
        }
        break;

    case "POST":
        $requestType = $requestData['type'] ?? '';
        if($requestType=="resident"){
            $created_by = $requestData['created_by'];
            $name = $requestData['name'];
            $plate = $requestData['plate'];
            $expiry = $requestData['expiry'];

            function generateRandomString($length = 10) {
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
            
                for ($i = 0; $i < $length; $i++) {
                    $randomString .= $characters[random_int(0, $charactersLength - 1)];
                }
            
                return $randomString;
            }



            $token = generateRandomString();
            $sql = "INSERT INTO `codes`(`token`, `created_by`, `expiry`, `intended_visitor`, `plate_id`) 
                    VALUES ('$token','$created_by','$expiry','$name','$plate')";

            if ($conn->query($sql)){
                QRcode::png($token,"qr/$token.png");
                echo json_encode(["message" => "QR Code Created","error"=>false]);
            }else{
                echo json_encode(["message" => "Failed to create QR Code: ".$conn->error,"error"=>true]);
            }

        }elseif($requestType=="guest"){
            $token = $conn->real_escape_string($requestData['token']);
            $scan_by = $conn->real_escape_string($requestData['scan_by']);
            $time = date("Y-m-d H:i:s");

            // Check if token exists and is not expired
            $codeQuery = $conn->prepare("SELECT * FROM codes WHERE token = ?");
            $codeQuery->bind_param("s", $token);
            $codeQuery->execute();
            $res = $codeQuery->get_result();
            if ($res->num_rows <= 0) {
                echo json_encode(["message" => "Invalid token."]);
                exit;
            }
            $code = $res->fetch_assoc();
            if (strtotime($code['expiry']) <= time()) {
                echo json_encode(["message" => "Token expired."]);
                exit;
            }

            // Count previous scans
            $scanCountQuery = $conn->prepare("SELECT COUNT(*) as count FROM logs WHERE token = ?");
            $scanCountQuery->bind_param("s", $token);
            $scanCountQuery->execute();
            $res = $scanCountQuery->get_result();
            $scanCount = (int)$res->fetch_assoc()['count'];
            $scan_type = $scanCount === 0 ? "In" : "Out";

            if ($scanCount >= 2) {
                echo json_encode(["message" => "Token already used twice (expired)."], JSON_PRETTY_PRINT);
                exit;
            }

            // Insert scan log
            $insertLogQuery = $conn->prepare("INSERT INTO logs (token, scan_time, scan_type, scan_by) VALUES (?, ?, ?, ?)");
            $insertLogQuery->bind_param("ssss", $token, $time, $scan_type, $scan_by);
            $insertLogQuery->execute();

            if ($scanCount === 0) {
                // Create notification for resident when visitor arrives
                $resident_id = $code['created_by'];
                $visitor_name = $code['intended_visitor'];
                $message = "Your visitor <b>$visitor_name</b> has arrived at $time";
                $stmt = $conn->prepare("INSERT INTO notifications (resident_id, message) VALUES (?,?)");
                $stmt->bind_param("is", $resident_id, $message);
                $stmt->execute();
                
                echo json_encode(["message" => "Login recorded."]);
            } elseif ($scanCount === 1) {
                echo json_encode(["message" => "Logout recorded."]);
            } else {
                echo json_encode(["message" => "Unexpected state."]);
            }
        }elseif($requestType=="chat"){
            $senderId = $requestData['sender_id'] ?? null;
            $receiverId = $requestData['receiver_id'] ?? null;
            $message = $requestData['message'] ?? '';
            $senderType = isset($requestData['sender_type']) ? strtolower($requestData['sender_type']) : (isset($_SESSION['type']) ? $_SESSION['type'] : null);
            $receiverType = isset($requestData['receiver_type']) ? strtolower($requestData['receiver_type']) : null;

            // Normalize allowed types
            $allowedTypes = ['resident', 'security'];
            if (!in_array($senderType, $allowedTypes, true)) {
                // Try to infer sender type from DB if session not available
                $senderType = null;
            }

            if ($senderId && $receiverId && $message !== '') {
                // If senderType still unknown, try DB lookups to determine role
                if (!$senderType) {
                    $check = $conn->prepare("SELECT id FROM residents WHERE id = ?");
                    $check->bind_param("i", $senderId);
                    if ($check->execute()) {
                        $res = $check->get_result();
                        if ($res && $res->num_rows > 0) {
                            $senderType = 'resident';
                        }
                    }
                    $check->close();
                }
                if (!$senderType) {
                    $check = $conn->prepare("SELECT id FROM security WHERE id = ?");
                    $check->bind_param("i", $senderId);
                    if ($check->execute()) {
                        $res = $check->get_result();
                        if ($res && $res->num_rows > 0) {
                            $senderType = 'security';
                        }
                    }
                    $check->close();
                }

                // If receiverType missing, infer from senderType
                if (!$receiverType) {
                    if ($senderType === 'resident') $receiverType = 'security';
                    elseif ($senderType === 'security') $receiverType = 'resident';
                }

                // Final fallbacks
                if (!in_array($senderType, $allowedTypes, true)) $senderType = 'resident';
                if (!in_array($receiverType, $allowedTypes, true)) $receiverType = ($senderType === 'resident' ? 'security' : 'resident');

                // Insert including sender/receiver types
                $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, sender_type, receiver_type) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisss", $senderId, $receiverId, $message, $senderType, $receiverType);

                if ($stmt->execute()) {
                    echo json_encode(['message' => 'Message sent successfully', 'error' => false]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => true, 'message' => 'Failed to send message: ' . $stmt->error]);
                }
                $stmt->close();
            } else {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Invalid chat payload']);
            }
        }elseif($requestType=="call"){
            $action = $requestData['action'] ?? '';

            if ($action === 'offer') {
                $securityId = $requestData['security_id'] ?? null;
                $residentId = $requestData['resident_id'] ?? null;
                $offer = isset($requestData['offer']) ? json_encode($requestData['offer']) : null;

                if ($securityId && $residentId && $offer) {
                    $stmt = $conn->prepare("INSERT INTO calls (security_id, resident_id, offer) VALUES (?, ?, ?)");
                    $stmt->bind_param("iis", $securityId, $residentId, $offer);

                    if ($stmt->execute()) {
                        echo json_encode(['message' => 'Call offer sent successfully', 'error' => false]);
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => true, 'message' => 'Failed to send call offer: ' . $stmt->error]);
                    }
                    $stmt->close();
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => true, 'message' => 'Invalid call offer payload']);
                }
            } elseif ($action === 'ice_candidate') {
                $securityId = $requestData['security_id'] ?? null;
                $residentId = $requestData['resident_id'] ?? null;
                $candidate = isset($requestData['candidate']) ? json_encode($requestData['candidate']) : null;

                if ($securityId && $residentId && $candidate) {
                    $stmt = $conn->prepare("UPDATE calls SET ice_candidates = CONCAT(COALESCE(ice_candidates, ''), ?) WHERE security_id = ? AND resident_id = ? AND status = 'pending'");
                    $stmt->bind_param("sii", $candidate, $securityId, $residentId);

                    if ($stmt->execute()) {
                        echo json_encode(['message' => 'ICE candidate added successfully', 'error' => false]);
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => true, 'message' => 'Failed to add ICE candidate: ' . $stmt->error]);
                    }
                    $stmt->close();
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => true, 'message' => 'Invalid ICE candidate payload']);
                }
            } else {
                echo json_encode(["message" => "Invalid call action","error"=>true]);
            }
        }elseif($requestType=="create_announcement"){
            // Validate CSRF token
            $csrf_token = $requestData['csrf_token'] ?? '';
            if (!validateCSRFToken($csrf_token)) {
                http_response_code(403);
                echo json_encode(['error' => true, 'message' => 'Invalid CSRF token']);
                exit;
            }

            // Check if admin is logged in
            if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => true, 'message' => 'Unauthorized access. Admin session required.']);
                exit;
            }

            $title = $requestData['title'] ?? '';
            $content = $requestData['content'] ?? '';

            // Validate input
            if (empty($title) || empty($content)) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Title and content are required']);
                exit;
            }

            if (strlen($title) > 255) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Title is too long (maximum 255 characters)']);
                exit;
            }

            // Insert announcement with prepared statement
            $stmt = $conn->prepare("INSERT INTO announcements (title, content, post_time) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $title, $content);

            if ($stmt->execute()) {
                echo json_encode(['message' => 'Announcement created successfully!', 'error' => false, 'id' => $conn->insert_id]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => true, 'message' => 'Failed to create announcement: ' . $stmt->error]);
            }
            $stmt->close();
        }elseif($requestType=="create_resident_invite"){
            // Validate CSRF token
            $csrf_token = $requestData['csrf_token'] ?? '';
            if (!validateCSRFToken($csrf_token)) {
                http_response_code(403);
                echo json_encode(['error' => true, 'message' => 'Invalid CSRF token']);
                exit;
            }

            // Check if admin is logged in
            if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => true, 'message' => 'Unauthorized access. Admin session required.']);
                exit;
            }

            $email = $requestData['email'] ?? '';
            $expiry_hours = (int)($requestData['expiry_hours'] ?? 24);

            // Validate input
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Valid email address is required']);
                exit;
            }

            // Generate unique invite code
            $invite_code = bin2hex(random_bytes(16));
            $expires_at = date('Y-m-d H:i:s', time() + ($expiry_hours * 3600));

            // Insert invite code
            $stmt = $conn->prepare("INSERT INTO invite_codes (code, email, user_type, expires_at, created_by) VALUES (?, ?, 'resident', ?, ?)");
            $admin_id = $_SESSION['id'];
            $stmt->bind_param("sssi", $invite_code, $email, $expires_at, $admin_id);

            if ($stmt->execute()) {
                $email_sent = false;
                
                // Try to send email
                try {
                    require_once 'email_config.php';
                    $email_sent = sendInviteEmail($email, $invite_code, 'resident', null, $expires_at);
                } catch (Exception $e) {
                    error_log('Email sending failed: ' . $e->getMessage());
                }

                echo json_encode([
                    'message' => 'Resident invite created successfully!',
                    'error' => false,
                    'invite_code' => $invite_code,
                    'expires_at' => $expires_at,
                    'email_sent' => $email_sent
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => true, 'message' => 'Failed to create invite: ' . $stmt->error]);
            }
            $stmt->close();
        }elseif($requestType=="create_security_invite"){
            // Validate CSRF token
            $csrf_token = $requestData['csrf_token'] ?? '';
            if (!validateCSRFToken($csrf_token)) {
                http_response_code(403);
                echo json_encode(['error' => true, 'message' => 'Invalid CSRF token']);
                exit;
            }

            // Check if admin is logged in
            if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => true, 'message' => 'Unauthorized access. Admin session required.']);
                exit;
            }

            $email = $requestData['email'] ?? '';
            $expiry_hours = (int)($requestData['expiry_hours'] ?? 24);

            // Validate input
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Valid email address is required']);
                exit;
            }

            // Generate unique invite code
            $invite_code = bin2hex(random_bytes(16));
            $expires_at = date('Y-m-d H:i:s', time() + ($expiry_hours * 3600));

            // Insert invite code
            $stmt = $conn->prepare("INSERT INTO invite_codes (code, email, user_type, expires_at, created_by) VALUES (?, ?, 'security', ?, ?)");
            $admin_id = $_SESSION['id'];
            $stmt->bind_param("sssi", $invite_code, $email, $expires_at, $admin_id);

            if ($stmt->execute()) {
                $email_sent = false;
                
                // Try to send email
                try {
                    require_once 'email_config.php';
                    $email_sent = sendInviteEmail($email, $invite_code, 'security', null, $expires_at);
                } catch (Exception $e) {
                    error_log('Email sending failed: ' . $e->getMessage());
                }

                echo json_encode([
                    'message' => 'Security invite created successfully!',
                    'error' => false,
                    'invite_code' => $invite_code,
                    'expires_at' => $expires_at,
                    'email_sent' => $email_sent
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => true, 'message' => 'Failed to create invite: ' . $stmt->error]);
            }
            $stmt->close();
        }elseif($requestType=="add_blacklist"){
            // Validate CSRF token
            $csrf_token = $requestData['csrf_token'] ?? '';
            if (!validateCSRFToken($csrf_token)) {
                http_response_code(403);
                echo json_encode(['error' => true, 'message' => 'Invalid CSRF token']);
                exit;
            }

            // Check if admin is logged in
            if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => true, 'message' => 'Unauthorized access. Admin session required.']);
                exit;
            }

            $plate = $requestData['plate'] ?? '';
            $reason = $requestData['reason'] ?? '';
            $created_by = $_SESSION['id'];

            // Validate input
            if (empty($plate)) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Car plate is required']);
                exit;
            }

            // Check if plate already exists in blacklist
            $checkStmt = $conn->prepare("SELECT id FROM blacklist WHERE blacklisted_car_plate = ?");
            $checkStmt->bind_param("s", $plate);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'This car plate is already in the blacklist']);
                $checkStmt->close();
                exit;
            }
            $checkStmt->close();

            // Insert blacklist entry
            $stmt = $conn->prepare("INSERT INTO blacklist (blacklisted_car_plate, reason, created_by) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $plate, $reason, $created_by);

            if ($stmt->execute()) {
                echo json_encode(['message' => 'Car plate added to blacklist successfully!', 'error' => false]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => true, 'message' => 'Failed to add to blacklist: ' . $stmt->error]);
            }
            $stmt->close();
        }elseif($requestType=="register_admin"){
            // Validate CSRF token
            $csrf_token = $requestData['csrf_token'] ?? '';
            if (!validateCSRFToken($csrf_token)) {
                http_response_code(403);
                echo json_encode(['error' => true, 'message' => 'Invalid CSRF token']);
                exit;
            }

            // Check if admin is logged in with level 2 or higher
            if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'admin' || $_SESSION['access_level'] < 2) {
                http_response_code(403);
                echo json_encode(['error' => true, 'message' => 'Unauthorized. Only super admins can register new admins.']);
                exit;
            }

            $username = $requestData['username'] ?? '';
            $email = $requestData['email'] ?? '';
            $password = $requestData['password'] ?? '';
            $access_level = (int)($requestData['access_level'] ?? 1);

            // Validate input
            if (empty($username) || strlen($username) < 3) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Username must be at least 3 characters long']);
                exit;
            }

            if (empty($password) || strlen($password) < 6) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Password must be at least 6 characters long']);
                exit;
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Valid email address is required']);
                exit;
            }

            if (!in_array($access_level, [1, 2])) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Invalid access level']);
                exit;
            }

            // Check if username already exists
            $checkStmt = $conn->prepare("SELECT id FROM admins WHERE user = ?");
            $checkStmt->bind_param("s", $username);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Username already exists']);
                $checkStmt->close();
                exit;
            }
            $checkStmt->close();

            // Check if email already exists
            $checkEmailStmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
            $checkEmailStmt->bind_param("s", $email);
            $checkEmailStmt->execute();
            $checkEmailResult = $checkEmailStmt->get_result();
            
            if ($checkEmailResult->num_rows > 0) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Email already exists']);
                $checkEmailStmt->close();
                exit;
            }
            $checkEmailStmt->close();

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new admin
            $stmt = $conn->prepare("INSERT INTO admins (user, pass, email, access_level) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $username, $hashedPassword, $email, $access_level);

            if ($stmt->execute()) {
                echo json_encode([
                    'message' => 'Admin registered successfully!',
                    'error' => false,
                    'id' => $conn->insert_id
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => true, 'message' => 'Failed to register admin: ' . $stmt->error]);
            }
            $stmt->close();
        }elseif($requestType=="register_with_invite"){
            // Validate CSRF token
            $csrf_token = $requestData['csrf_token'] ?? '';
            if (!validateCSRFToken($csrf_token)) {
                http_response_code(403);
                echo json_encode(['error' => true, 'message' => 'Invalid CSRF token']);
                exit;
            }

            $invite_code = $requestData['invite_code'] ?? '';
            $email = $requestData['email'] ?? '';
            $username = $requestData['user'] ?? '';
            $password = $requestData['pass'] ?? '';

            // Validate input
            if (empty($invite_code) || empty($email) || empty($username) || empty($password)) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'All fields are required']);
                exit;
            }

            if (strlen($password) < 6) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Password must be at least 6 characters long']);
                exit;
            }

            // Check if invite code exists and is valid
            $stmt = $conn->prepare("SELECT id, email, user_type, expires_at, is_used, room_code FROM invite_codes WHERE code = ?");
            $stmt->bind_param("s", $invite_code);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Invalid invite code']);
                $stmt->close();
                exit;
            }

            $invite = $result->fetch_assoc();
            $stmt->close();

            // Check if already used
            if ($invite['is_used'] == 1) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'This invite code has already been used']);
                exit;
            }

            // Check if expired
            if (strtotime($invite['expires_at']) < time()) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'This invite code has expired']);
                exit;
            }

            // Check if email matches
            if (strtolower($invite['email']) !== strtolower($email)) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Email does not match the invite code']);
                exit;
            }

            $user_type = $invite['user_type'];
            $room_code = $invite['room_code'];
            
            // Allow override of room_code if provided in request (for empty invite room codes)
            if (isset($requestData['room_code']) && !empty($requestData['room_code'])) {
                $room_code = $requestData['room_code'];
            }

            // Check if username already exists in the appropriate table
            if ($user_type === 'resident') {
                $checkStmt = $conn->prepare("SELECT id FROM residents WHERE user = ?");
            } else {
                $checkStmt = $conn->prepare("SELECT id FROM security WHERE user = ?");
            }
            $checkStmt->bind_param("s", $username);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Username already exists']);
                $checkStmt->close();
                exit;
            }
            $checkStmt->close();

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into appropriate table
            if ($user_type === 'resident') {
                // Ensure room_code is not null - use empty string if not provided
                if (empty($room_code)) {
                    $room_code = '';
                }
                $insertStmt = $conn->prepare("INSERT INTO residents (user, pass, email, room_code, is_active) VALUES (?, ?, ?, ?, 1)");
                $insertStmt->bind_param("ssss", $username, $hashedPassword, $email, $room_code);
            } else {
                $insertStmt = $conn->prepare("INSERT INTO security (user, pass, email, is_active) VALUES (?, ?, ?, 1)");
                $insertStmt->bind_param("sss", $username, $hashedPassword, $email);
            }

            if ($insertStmt->execute()) {
                // Mark invite code as used
                $updateStmt = $conn->prepare("UPDATE invite_codes SET is_used = 1, used_at = NOW() WHERE id = ?");
                $updateStmt->bind_param("i", $invite['id']);
                $updateStmt->execute();
                $updateStmt->close();

                echo json_encode([
                    'message' => 'Registration successful! You can now log in.',
                    'error' => false,
                    'user_type' => $user_type
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => true, 'message' => 'Failed to create account: ' . $insertStmt->error]);
            }
            $insertStmt->close();
        }else{
            echo json_encode(["message" => "Type not recognized","error"=>true]);
        }
        break;
    case "PUT":
        $requestType = $requestData['type'] ?? '';
        if($requestType=="resident"){
            if(isset($requestData['id']) && !isset($requestData['name'])) {
                // Handle marking notification as read
                $id = $requestData['id'];
                $sql = "UPDATE notifications SET is_read = 1 WHERE id = '$id'";
                
                if ($conn->query($sql)){
                    echo json_encode(["message" => "Notification marked as read","error"=>false]);
                }else{
                    echo json_encode(["message" => "Failed to mark notification as read: ".$conn->error,"error"=>true]);
                }
            } else {
                // Handle editing QR code
                $id = $requestData['id'];
                $name = $requestData['name'];
                $plate = $requestData['plate'];
                $expiry = $requestData['expiry'];

                $sql = "UPDATE `codes` SET `expiry`='$expiry',`intended_visitor`='$name',`plate_id`='$plate' WHERE `id` = '$id'";

                if ($conn->query($sql)){
                    echo json_encode(["message" => "QR Code Editted","error"=>false]);
                }else{
                    echo json_encode(["message" => "Failed to edit QR Code: ".$conn->error,"error"=>true]);
                }
            }
        }elseif($requestType=="admin"){

        }elseif($requestType=="call"){
            $securityId = $requestData['security_id'] ?? null;
            $residentId = $requestData['resident_id'] ?? null;
            $answer = isset($requestData['answer']) ? json_encode($requestData['answer']) : null;

            if ($securityId && $residentId && $answer) {
                $stmt = $conn->prepare("UPDATE calls SET answer = ?, status = 'active' WHERE security_id = ? AND resident_id = ? AND status = 'pending'");
                $stmt->bind_param("sii", $answer, $securityId, $residentId);

                if ($stmt->execute()) {
                    echo json_encode(['message' => 'Call answer sent successfully', 'error' => false]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => true, 'message' => 'Failed to send call answer: ' . $stmt->error]);
                }
                $stmt->close();
            } else {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Invalid call answer payload']);
            }
        }else{
            echo json_encode(["message" => "Type not recognized","error"=>true]);
        }
        break;

    case "DELETE":
        if($requestData['type']=="resident"){
            $id = $requestData['id'] ?? 0;

            if (!$id) {
                http_response_code(400);
                echo json_encode(["message" => "Invalid input"]);
                exit;
            }

            $sql = "DELETE FROM codes WHERE id = $id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(["message" => "QR deleted successfully","error"=>false]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error deleting QR: " . $conn->error,"error"=>true]);
            }
        }elseif($requestData['type']=="admin"){
            // Validate CSRF token
            $csrf_token = $requestData['csrf_token'] ?? '';
            if (!validateCSRFToken($csrf_token)) {
                http_response_code(403);
                echo json_encode(['error' => true, 'message' => 'Invalid CSRF token']);
                exit;
            }

            // Check if admin is logged in
            if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => true, 'message' => 'Unauthorized access']);
                exit;
            }

            $id = $requestData['id'] ?? 0;
            $fetch = $requestData['fetch'] ?? '';

            if (!$id) {
                http_response_code(400);
                echo json_encode(["message" => "Invalid input", "error" => true]);
                exit;
            }

            if($fetch == 'admin'){
                $sql = "DELETE FROM admins WHERE id = $id";
                $successMsg = "Admin deleted successfully";
            }elseif($fetch == 'resident'){
                $sql = "DELETE FROM residents WHERE id = $id";
                $successMsg = "Resident deleted successfully";
            }elseif($fetch == 'security'){
                $sql = "DELETE FROM security WHERE id = $id";
                $successMsg = "Security staff deleted successfully";
            }elseif($fetch == 'blacklist'){
                $sql = "DELETE FROM blacklist WHERE id = $id";
                $successMsg = "Blacklist entry removed successfully";
            }elseif($fetch == 'announcement'){
                $sql = "DELETE FROM announcements WHERE id = $id";
                $successMsg = "Announcement deleted successfully";
            }elseif($fetch == 'invite_code'){
                $sql = "DELETE FROM invite_codes WHERE id = $id AND is_used = 0";
                $successMsg = "Invite code deleted successfully";
            }else{
                http_response_code(400);
                echo json_encode(["message" => "Invalid delete target", "error" => true]);
                exit;
            }
                
            if ($conn->query($sql) === TRUE) {
                echo json_encode(["message" => $successMsg, "error" => false]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error deleting record: " . $conn->error, "error" => true]);
            }
        }else{
            echo json_encode(["message" => "Type not recognized","error"=>true]);
        }
        break; 
    default:
        http_response_code(405); // method not allowed
        echo json_encode(["message" => "Method not allowed","error"=>true]);
        break;
}