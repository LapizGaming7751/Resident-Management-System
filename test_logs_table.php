<?php
// Test script to check logs table structure
$host = 'localhost';
$db = 'resident_management_system';
$db_user = 'root';
$db_pass = '';

$conn = new mysqli($host, $db_user, $db_pass, $db);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

echo "=== LOGS TABLE STRUCTURE ===\n";
$result = $conn->query("DESCRIBE logs");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n=== SAMPLE LOG DATA ===\n";
$result = $conn->query("SELECT * FROM logs LIMIT 3");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "No logs found or error: " . $conn->error . "\n";
}

$conn->close();
?>
