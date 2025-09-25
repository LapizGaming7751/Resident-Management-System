<?php
// Test script to check if blacklist table exists
$host = 'localhost';
$db = 'synergy1_siewyaoying_resident_management';
$db_user = 'synergy1';
$db_pass = 'Hu49xW-b[8lY0R';

$conn = new mysqli($host, $db_user, $db_pass, $db);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Check if blacklist table exists
$result = $conn->query("SHOW TABLES LIKE 'blacklist'");
if ($result->num_rows > 0) {
    echo "✅ Blacklist table exists\n";
    
    // Check table structure
    $result = $conn->query("DESCRIBE blacklist");
    echo "Table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "❌ Blacklist table does NOT exist\n";
    echo "You need to run the database update script first!\n";
    echo "Run this SQL:\n";
    echo "CREATE TABLE IF NOT EXISTS `blacklist` (\n";
    echo "  `id` int(255) NOT NULL AUTO_INCREMENT,\n";
    echo "  `blacklisted_car_plate` varchar(255) NOT NULL,\n";
    echo "  `created_at` datetime NOT NULL DEFAULT current_timestamp(),\n";
    echo "  `created_by` int(255) NOT NULL,\n";
    echo "  PRIMARY KEY (`id`),\n";
    echo "  UNIQUE KEY `blacklisted_car_plate` (`blacklisted_car_plate`),\n";
    echo "  KEY `created_by` (`created_by`)\n";
    echo ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\n";
}

$conn->close();
?>
