<?php
// Unified API for Finals_CheckInSystem
// Cleaner, more maintainable version of api.php

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
            if ($id && ($fetch === '' || $fetch === 'qr' || !isset($_GET['fetch']))) {
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
        if ($type === "register_resident") {
            $user = $requestData['user'] ?? '';
            $pass = password_hash($requestData['pass'] ?? '', PASSWORD_DEFAULT);
            $room_code = $requestData['room_code'] ?? '';
            // ...validation logic...
            $sql = "INSERT INTO residents (user, pass, room_code) VALUES ('$user', '$pass', '$room_code')";
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
            echo json_encode(insertRow($conn, $sql));
        } elseif ($type === "chat") {
            $sender_id = $requestData['sender_id'] ?? '';
            $sender_type = $requestData['sender_type'] ?? '';
            $receiver_id = $requestData['receiver_id'] ?? '';
            $receiver_type = $requestData['receiver_type'] ?? '';
            $message = $conn->real_escape_string($requestData['message'] ?? '');
            $sql = "INSERT INTO messages (sender_id, sender_type, receiver_id, receiver_type, message) VALUES ('$sender_id', '$sender_type', '$receiver_id', '$receiver_type', '$message')";
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
                echo json_encode(updateRow($conn, $sql));
            }
        }
        break;
    case "DELETE":
        $type = $requestData['type'] ?? '';
        if ($type === "resident") {
            $id = $requestData['id'] ?? '';
            $sql = "DELETE FROM codes WHERE id = '$id'";
            echo json_encode(deleteRow($conn, $sql));
        } elseif ($type === "admin") {
            $id = $requestData['id'] ?? '';
            $fetch = $requestData['fetch'] ?? '';
            if ($fetch === 'admin') {
                $sql = "DELETE FROM admins WHERE id = '$id'";
            } elseif ($fetch === 'resident') {
                $sql = "DELETE FROM residents WHERE id = '$id'";
            }
            echo json_encode(deleteRow($conn, $sql));
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed", "error" => true]);
        break;
}
