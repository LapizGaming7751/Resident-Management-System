<?php
// login_handler.php for security guard
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $conn = new mysqli('localhost', 'synergy1', 'Hu49xW-b[8lY0R', 'synergy1_siewyaoying_resident_management');
    if ($conn->connect_error) {
        die('Database connection failed');
    }
    $sql = "SELECT * FROM security WHERE user = '" . $conn->real_escape_string($user) . "'";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        if (password_verify($pass, $row['pass'])) {
            $_SESSION['id'] = $row['id'];
            $_SESSION['user'] = $row['user'];
            $_SESSION['type'] = 'security';
                header('Location: scanner.php');
            exit;
        }
    }
    header('Location: index.php?error=1');
    exit;
}
?>
