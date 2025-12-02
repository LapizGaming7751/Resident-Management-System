<?php
// admin/login_handler.php

// Include secure configuration
require_once '../config.php';

configureSecureSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting for login attempts
    if (!checkRateLimit('admin_login', 5, 300)) { // 5 attempts per 5 minutes
        header('Location: index.php?error=rate_limit');
        exit;
    }
    
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    
    try {
        $conn = getDatabaseConnection();
    } catch (Exception $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        header('Location: index.php?error=db_error');
        exit;
    }
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, user, pass, access_level, email FROM admins WHERE user = ?");
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        if (password_verify($pass, $row['pass'])) {
            $_SESSION['id'] = $row['id'];
            $_SESSION['user'] = $row['user'];
            $_SESSION['access_level'] = $row['access_level'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['type'] = 'admin';
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            header('Location: manage.php');
            exit;
        }
    }
    
    $stmt->close();
    header('Location: index.php?error=1');
    exit;
}
?>
