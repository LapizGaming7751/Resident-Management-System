<?php
// Unified API for Finals_CheckInSystem
// Fixed version with proper announcement CRUD operations

date_default_timezone_set("Singapore");
include("phpqrcode/qrlib.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db = 'finals_scanner';
$db_user = 'root';
$db_pass = '';

$conn = new mysqli($host, $db_user, $db_pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed: " . $conn->connect_error]);
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
                        $list = fetchList($conn, "SELECT logs.id, logs.token, logs.scan_time, logs.scan_type, logs.scan_by, security.user AS scanner_username, codes.intended_visitor AS intended_visitor FROM logs LEFT JOIN security ON logs.scan_by = security.id LEFT JOIN codes ON logs.token = codes.token");
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
        
        if ($type === "register_resident") {
            $user = $requestData['user'] ?? '';
            $pass = password_hash($requestData['pass'] ?? '', PASSWORD_DEFAULT);
            $room_code = $requestData['room_code'] ?? '';
            // ...validation logic...
            $sql = "INSERT INTO residents (user, pass, room_code) VALUES ('$user', '$pass', '$room_code')";
            echo json_encode(insertRow($conn, $sql));
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
            
            echo json_encode([
                "message" => "QR code created successfully", 
                "error" => false, 
                "id" => $insert_id, 
                "token" => $token
            ]);
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