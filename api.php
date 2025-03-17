<?php
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
error_log("Request Data: " . json_encode($requestData));

$time = date("Y-m-d H:i:s");

switch ($method) {

    case "GET":
        if ($_GET['type']=="resident"){
            if(isset($_GET['created_by'])){
                $id = $_GET['created_by'];

                $sql = "SELECT * FROM codes WHERE created_by = '$id'";
                $result = $conn->query($sql);

                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $qr[] = $row;
                    }
                    echo json_encode($qr);
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Error fetching QR codes: " . $conn->error,"error"=>true]);
                }
            }else{
                $user = $_GET["user"] ?? "";
                $pass = $_GET["pass"] ?? "";
                error_log("Resident login attempt: user=$user, pass=$pass");

                $sql = "SELECT * FROM residents WHERE user = '$user'";
                $result = $conn->query($sql);

                if ($result) {
                    $row = $result->fetch_assoc();
                    if ($row && password_verify($pass, $row['pass'])) {
                        echo json_encode(["message" => "Welcome, " . $row['user'] . ".","error"=>false]);

                        $_SESSION['id'] = $row['id'];
                        $_SESSION["user"] = $row['user'];
                        $_SESSION["room_code"] = $row['room_code'];
                    } else {
                        echo json_encode(["message" => "Resident not found","error"=>true]);
                    }
                } else {
                    echo json_encode(["message" => "Query failed: " . $conn->error,"error"=>true]);
                }
            }

        }elseif ($_GET['type']=="admin"){
            if (isset($_GET['fetch'])){
                switch ($_GET['fetch']){
                    case "resident":
                        $sql = "SELECT * FROM residents";
                        $result = $conn->query($sql);

                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                $resident[] = $row;
                            }
                            echo json_encode($resident);
                        } else {
                            http_response_code(500);
                            echo json_encode(["message" => "Error fetching Residents: " . $conn->error,"error"=>true]);
                        }
                        break;
                    case "admin":
                        $sql = "SELECT * FROM admins";
                        $result = $conn->query($sql);

                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                $admin[] = $row;
                            }
                            echo json_encode($admin);
                        } else {
                            http_response_code(500);
                            echo json_encode(["message" => "Error fetching Admins: " . $conn->error,"error"=>true]);
                        }
                        break;
                    case "log":
                        $sql = "SELECT logs.id, logs.token, logs.scan_time, logs.scan_by, security.user AS scanner_username
                        FROM logs
                        LEFT JOIN security ON logs.scan_by = security.id";
                        $result = $conn->query($sql);

                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                $log[] = $row;
                            }
                            echo json_encode($log);
                        } else {
                            http_response_code(500);
                            echo json_encode(["message" => "Error fetching Logs: " . $conn->error,"error"=>true]);
                        }
                        break;
                    default:
                        echo json_encode(["message" => "Fetch not recognized","error"=>true]);
                        break;
                }
            } else {
                $user = $_GET["user"] ?? "";
                $pass = $_GET["pass"] ?? "";
                error_log("Admin login attempt: user=$user, pass=$pass");

                $sql = "SELECT * FROM admins WHERE user = '$user'";
                $result = $conn->query($sql);

                if ($result) {
                    $row = $result->fetch_assoc();
                    if ($row && password_verify($pass, $row['pass'])) {
                        echo json_encode(["message" => "Welcome, " . $row['user'] . ".","error"=>false]);

                        $_SESSION['id'] = $row['id'];
                        $_SESSION["user"] = $row['user'];
                        $_SESSION['access_level'] = $row['access_level'];
                        $_SESSION['type'] = "admin";
                        
                    } else {
                        echo json_encode(["message" => "Admin not found","error"=>true]);
                    }
                } else {
                    echo json_encode(["message" => "Query failed: " . $conn->error,"error"=>true]);
                }
            }

        }elseif ($_GET['type']=="security"){
            $user = $_GET["user"] ?? "";
            $pass = $_GET["pass"] ?? "";
            error_log("Security login attempt: user=$user, pass=$pass");

            $sql = "SELECT * FROM security WHERE user = '$user'";
            $result = $conn->query($sql);

            if ($result) {
                $row = $result->fetch_assoc();
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
        }else{
            echo json_encode(["message" => "Type not recognized","error"=>true]);
        }
        break;

    case "POST":
        if($requestData['type']=="resident"){
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

        }elseif($requestData['type']=="guest"){
            $token = $conn->real_escape_string($requestData['token']);
            $scan_by = $conn->real_escape_string($requestData['scan_by']);
            $time = date("Y-m-d H:i:s");

            // Check if token exists and is not expired
            $codeQuery = $conn->query("SELECT * FROM codes WHERE token = '$token'");
            if ($codeQuery->num_rows <= 0) {
                echo json_encode(["message" => "Invalid token."]);
                exit;
            }
            $code = $codeQuery->fetch_assoc();
            if (strtotime($code['expiry']) <= time()) {
                echo json_encode(["message" => "Token expired."]);
                exit;
            }

            // Count previous scans
            $scanCountQuery = $conn->query("SELECT COUNT(*) as count FROM logs WHERE token = '$token'");
            $scanCount = (int)$scanCountQuery->fetch_assoc()['count'];

            if ($scanCount >= 2) {
                echo json_encode(["message" => "Token already used twice (expired)."], JSON_PRETTY_PRINT);
                exit;
            }

            // Insert scan log
            $conn->query("INSERT INTO logs (token, scan_time, scan_by) VALUES ('$token', '$time', '$scan_by')");

            if ($scanCount === 0) {
                echo json_encode(["message" => "Login recorded."]);
            } elseif ($scanCount === 1) {
                echo json_encode(["message" => "Logout recorded."]);
            } else {
                echo json_encode(["message" => "Unexpected state."]);
            }
        }
        break;
    case "PUT":
        if($requestData['type']=="resident"){
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

        }elseif($requestData['type']=="admin"){

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
            $id = $requestData['id'] ?? 0;

            if (!$id) {
                http_response_code(400);
                echo json_encode(["message" => "Invalid input"]);
                exit;
            }

            if($requestData['fetch']=='admin'){
                $sql = "DELETE FROM admins WHERE id = $id";
            }elseif($requestData['fetch']=='resident'){
                $sql = "DELETE FROM residents WHERE id = $id";
            }
                
            if ($conn->query($sql) === TRUE) {
                echo json_encode(["message" => "Resident/Admin deleted successfully","error"=>false]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error deleting Resident/Admin: " . $conn->error,"error"=>true]);
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