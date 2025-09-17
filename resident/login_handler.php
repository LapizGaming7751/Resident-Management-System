<?php
// resident/login_handler.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    
    if (empty($user) || empty($pass)) {
        header('Location: index.php?error=missing_fields');
        exit;
    }
    
    $conn = new mysqli('localhost', 'root', '', 'finals_scanner');
    if ($conn->connect_error) {
        error_log('Database connection failed: ' . $conn->connect_error);
        header('Location: index.php?error=db_error');
        exit;
    }
    
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, user, pass, room_code, email FROM residents WHERE user = ?");
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        if (password_verify($pass, $row['pass'])) {
            $_SESSION['id'] = $row['id'];
            $_SESSION['user'] = $row['user'];
            $_SESSION['room_code'] = $row['room_code'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['type'] = 'resident';
            
            // Clear any existing reset tokens after successful login
            $clearStmt = $conn->prepare("UPDATE residents SET reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            $clearStmt->bind_param('i', $row['id']);
            $clearStmt->execute();
            $clearStmt->close();
            
            header('Location: manage.php');
            exit;
        }
    }
    
    $stmt->close();
    $conn->close();
    
    // Login failed
    header('Location: index.php?error=invalid_credentials');
    exit;
}

// Redirect if not POST request
header('Location: index.php');
exit;
?>