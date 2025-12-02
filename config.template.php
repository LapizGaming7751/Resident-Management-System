<?php
// Configuration template file
// Copy this to config.php and update with your actual values
// Make sure to add config.php to .gitignore

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'resident_management_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_app_password');
define('FROM_EMAIL', 'your_email@gmail.com');
define('FROM_NAME', 'Check-In System');

// Application Configuration
define('APP_URL', 'https://your-domain.com/ResidentManagementSystem');
define('APP_TIMEZONE', 'Asia/Singapore');

// Security Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('PASSWORD_RESET_EXPIRY', 3600); // 1 hour
define('INVITE_CODE_EXPIRY', 86400); // 24 hours

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Database connection function
function getDatabaseConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            throw new Exception("Database connection failed");
        }
        
        // Set charset to utf8mb4 for proper Unicode support
        $conn->set_charset("utf8mb4");
    }
    
    return $conn;
}

// Secure session configuration
function configureSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        session_start();
        
        // Regenerate session ID periodically for security
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}
?>
