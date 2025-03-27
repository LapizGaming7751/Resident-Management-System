<?php
date_default_timezone_set("Singapore");

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

switch ($method) {
    case "GET":
        if ($_GET['type'] == 'chat' && isset($_GET['fetch']) && $_GET['fetch'] == 'messages') {
            $security_id = $_GET['security_id'];
            $resident_id = $_GET['resident_id'];
            
            $stmt = $conn->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
            $stmt->bind_param("iiii", $security_id, $resident_id, $resident_id, $security_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
            
            echo json_encode($messages);
        } else if ($_GET['type'] == 'call' && isset($_GET['action']) && $_GET['action'] == 'get_answer') {
            $security_id = $_GET['security_id'];
            $resident_id = $_GET['resident_id'];
            
            $stmt = $conn->prepare("SELECT answer FROM calls WHERE security_id = ? AND resident_id = ? AND status = 'pending'");
            $stmt->bind_param("ii", $security_id, $resident_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                echo json_encode(['answer' => json_decode($row['answer'])]);
            } else {
                echo json_encode(['answer' => null]);
            }
        } else {
            echo json_encode(["message" => "Invalid request","error"=>true]);
        }
        break;

    case "POST":
        if ($requestData['type'] == 'chat') {
            $sender_id = $requestData['sender_id'];
            $receiver_id = $requestData['receiver_id'];
            $message = $requestData['message'];

            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
            
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Message sent successfully']);
            } else {
                echo json_encode(['error' => true, 'message' => 'Failed to send message']);
            }
        } else if ($requestData['type'] == 'call') {
            if ($requestData['action'] == 'offer') {
                $security_id = $requestData['security_id'];
                $resident_id = $requestData['resident_id'];
                $offer = json_encode($requestData['offer']);
                
                $stmt = $conn->prepare("INSERT INTO calls (security_id, resident_id, offer) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $security_id, $resident_id, $offer);
                
                if ($stmt->execute()) {
                    echo json_encode(['message' => 'Call offer sent successfully']);
                } else {
                    echo json_encode(['error' => true, 'message' => 'Failed to send call offer']);
                }
            } else if ($requestData['action'] == 'ice_candidate') {
                $security_id = $requestData['security_id'];
                $resident_id = $requestData['resident_id'];
                $candidate = json_encode($requestData['candidate']);
                
                $stmt = $conn->prepare("UPDATE calls SET ice_candidates = CONCAT(COALESCE(ice_candidates, ''), ?) WHERE security_id = ? AND resident_id = ? AND status = 'pending'");
                $stmt->bind_param("sii", $candidate, $security_id, $resident_id);
                
                if ($stmt->execute()) {
                    echo json_encode(['message' => 'ICE candidate added successfully']);
                } else {
                    echo json_encode(['error' => true, 'message' => 'Failed to add ICE candidate']);
                }
            }
        } else {
            echo json_encode(["message" => "Invalid request","error"=>true]);
        }
        break;

    case "PUT":
        if ($requestData['type'] == 'call') {
            $security_id = $requestData['security_id'];
            $resident_id = $requestData['resident_id'];
            $answer = json_encode($requestData['answer']);
            
            $stmt = $conn->prepare("UPDATE calls SET answer = ?, status = 'active' WHERE security_id = ? AND resident_id = ? AND status = 'pending'");
            $stmt->bind_param("sii", $answer, $security_id, $resident_id);
            
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Call answer sent successfully']);
            } else {
                echo json_encode(['error' => true, 'message' => 'Failed to send call answer']);
            }
        } else {
            echo json_encode(["message" => "Invalid request","error"=>true]);
        }
        break;

    default:
        echo json_encode(["message" => "Method not allowed","error"=>true]);
        break;
}
?> 