<?php
// resident/login_handler.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $conn = new mysqli('localhost', 'root', '', 'finals_scanner');
    if ($conn->connect_error) {
        die('Database connection failed');
    }
    $sql = "SELECT * FROM residents WHERE user = '" . $conn->real_escape_string($user) . "'";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        if (password_verify($pass, $row['pass'])) {
            $_SESSION['id'] = $row['id'];
            $_SESSION['user'] = $row['user'];
            $_SESSION['room_code'] = $row['room_code'];
            $_SESSION['type'] = 'resident';
            header('Location: manage.php');
            exit;
        }
    }
    header('Location: index.php?error=1');
    exit;
}
?>
