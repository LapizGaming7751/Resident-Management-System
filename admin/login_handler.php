<?php
// admin/login_handler.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $conn = new mysqli('localhost', 'synergy1', 'Hu49xW-b[8lY0R', 'synergy1_siewyaoying_resident_management');
    if ($conn->connect_error) {
        die('Database connection failed');
    }
    $sql = "SELECT * FROM admins WHERE user = '" . $conn->real_escape_string($user) . "'";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        if (password_verify($pass, $row['pass'])) {
            $_SESSION['id'] = $row['id'];
            $_SESSION['user'] = $row['user'];
            $_SESSION['access_level'] = $row['access_level'];
            $_SESSION['type'] = 'admin';
            header('Location: manage.php');
            exit;
        }
    }
    header('Location: index.php?error=1');
    exit;
}
?>
