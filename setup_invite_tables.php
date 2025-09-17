<?php
// Setup script to create invite_codes table and update existing tables
$host = 'localhost';
$db = 'finals_scanner';
$db_user = 'root';
$db_pass = '';

$conn = new mysqli($host, $db_user, $db_pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

echo "<h2>Setting up Invite Code System</h2>\n";

// Check if invite_codes table exists
$result = $conn->query("SHOW TABLES LIKE 'invite_codes'");
if ($result->num_rows == 0) {
    echo "<p>Creating invite_codes table...</p>\n";
    
    $sql = "CREATE TABLE `invite_codes` (
        `id` int(255) NOT NULL AUTO_INCREMENT,
        `code` varchar(255) NOT NULL,
        `user_type` enum('resident','security') NOT NULL,
        `email` varchar(255) NOT NULL,
        `room_code` varchar(255) DEFAULT NULL,
        `created_by` int(255) NOT NULL,
        `created_at` datetime NOT NULL DEFAULT current_timestamp(),
        `expires_at` datetime NOT NULL,
        `is_used` tinyint(1) NOT NULL DEFAULT 0,
        `used_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `code` (`code`),
        KEY `created_by` (`created_by`),
        CONSTRAINT `invite_codes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ invite_codes table created successfully</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Error creating invite_codes table: " . $conn->error . "</p>\n";
    }
} else {
    echo "<p style='color: blue;'>ℹ invite_codes table already exists</p>\n";
}

// Check if residents table has email column
$result = $conn->query("SHOW COLUMNS FROM residents LIKE 'email'");
if ($result->num_rows == 0) {
    echo "<p>Adding email column to residents table...</p>\n";
    
    $sql = "ALTER TABLE `residents` ADD COLUMN `email` varchar(255) NOT NULL DEFAULT '' AFTER `pass`";
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Email column added to residents table</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Error adding email column: " . $conn->error . "</p>\n";
    }
} else {
    echo "<p style='color: blue;'>ℹ Email column already exists in residents table</p>\n";
}

// Check if residents table has is_active column
$result = $conn->query("SHOW COLUMNS FROM residents LIKE 'is_active'");
if ($result->num_rows == 0) {
    echo "<p>Adding is_active column to residents table...</p>\n";
    
    $sql = "ALTER TABLE `residents` ADD COLUMN `is_active` tinyint(1) NOT NULL DEFAULT 1 AFTER `room_code`";
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ is_active column added to residents table</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Error adding is_active column: " . $conn->error . "</p>\n";
    }
} else {
    echo "<p style='color: blue;'>ℹ is_active column already exists in residents table</p>\n";
}

// Check if security table exists
$result = $conn->query("SHOW TABLES LIKE 'security'");
if ($result->num_rows == 0) {
    echo "<p>Creating security table...</p>\n";
    
    $sql = "CREATE TABLE `security` (
        `id` int(255) NOT NULL AUTO_INCREMENT,
        `user` varchar(255) NOT NULL,
        `pass` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
        `is_active` tinyint(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ security table created successfully</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Error creating security table: " . $conn->error . "</p>\n";
    }
} else {
    echo "<p style='color: blue;'>ℹ security table already exists</p>\n";
}

// Check if security table has email column
$result = $conn->query("SHOW COLUMNS FROM security LIKE 'email'");
if ($result->num_rows == 0) {
    echo "<p>Adding email column to security table...</p>\n";
    
    $sql = "ALTER TABLE `security` ADD COLUMN `email` varchar(255) NOT NULL DEFAULT '' AFTER `pass`";
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Email column added to security table</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Error adding email column: " . $conn->error . "</p>\n";
    }
} else {
    echo "<p style='color: blue;'>ℹ Email column already exists in security table</p>\n";
}

// Check if security table has is_active column
$result = $conn->query("SHOW COLUMNS FROM security LIKE 'is_active'");
if ($result->num_rows == 0) {
    echo "<p>Adding is_active column to security table...</p>\n";
    
    $sql = "ALTER TABLE `security` ADD COLUMN `is_active` tinyint(1) NOT NULL DEFAULT 1 AFTER `email`";
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ is_active column added to security table</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Error adding is_active column: " . $conn->error . "</p>\n";
    }
} else {
    echo "<p style='color: blue;'>ℹ is_active column already exists in security table</p>\n";
}

echo "<h3>Setup Complete!</h3>\n";
echo "<p>You can now try creating invite codes from the admin panel.</p>\n";

$conn->close();
?>
