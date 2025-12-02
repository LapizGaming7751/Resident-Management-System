<?php
// Test script for notification system
session_start();

// Set up database connection
$host = 'localhost';
$db = 'synergy1_siewyaoying_resident_management';
$db_user = 'synergy1';
$db_pass = 'Hu49xW-b8lY0R';

$conn = new mysqli($host, $db_user, $db_pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

echo "<h2>Notification System Test</h2>";

// Test 1: Check if notifications table exists and has data
echo "<h3>Test 1: Database Structure</h3>";
$result = $conn->query("SHOW TABLES LIKE 'notifications'");
if ($result->num_rows > 0) {
    echo "✅ Notifications table exists<br>";
    
    // Check table structure
    $columns = $conn->query("SHOW COLUMNS FROM notifications");
    echo "Table structure:<br>";
    while ($col = $columns->fetch_assoc()) {
        echo "- {$col['Field']} ({$col['Type']})<br>";
    }
} else {
    echo "❌ Notifications table does not exist<br>";
}

// Test 2: Check for existing notifications
echo "<h3>Test 2: Existing Notifications</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM notifications");
$count = $result->fetch_assoc()['count'];
echo "Total notifications in database: $count<br>";

if ($count > 0) {
    $result = $conn->query("SELECT n.*, r.user as resident_name FROM notifications n LEFT JOIN residents r ON n.resident_id = r.id ORDER BY n.created_at DESC LIMIT 5");
    echo "Recent notifications:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "- [{$row['created_at']}] {$row['resident_name']}: {$row['message']} (Read: " . ($row['is_read'] ? 'Yes' : 'No') . ")<br>";
    }
}

// Test 3: Check residents table
echo "<h3>Test 3: Available Residents</h3>";
$result = $conn->query("SELECT id, user, room_code FROM residents LIMIT 5");
echo "Available residents:<br>";
while ($row = $result->fetch_assoc()) {
    echo "- ID {$row['id']}: {$row['user']} (Room: {$row['room_code']})<br>";
}

// Test 4: Simulate notification creation
echo "<h3>Test 4: Simulate Notification Creation</h3>";
$test_message = "Test notification created at " . date('Y-m-d H:i:s');
$resident_id = 1; // Assuming resident ID 1 exists

$sql = "INSERT INTO notifications (resident_id, message) VALUES ('$resident_id', '$test_message')";
if ($conn->query($sql)) {
    echo "✅ Test notification created successfully<br>";
    echo "Message: $test_message<br>";
} else {
    echo "❌ Failed to create test notification: " . $conn->error . "<br>";
}

// Test 5: Check API endpoints
echo "<h3>Test 5: API Endpoint Test</h3>";
echo "API URL: <a href='api.php?type=resident&created_by=1&fetch=notifications' target='_blank'>api.php?type=resident&created_by=1&fetch=notifications</a><br>";
echo "This should return notifications for resident ID 1 in JSON format.<br>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
</style>
