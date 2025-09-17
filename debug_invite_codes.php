<?php
// Debug script to check invite_codes table structure and data
$host = 'localhost';
$db = 'finals_scanner';
$db_user = 'root';
$db_pass = '';

$conn = new mysqli($host, $db_user, $db_pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

echo "<h2>Debug Invite Codes Table</h2>\n";

// Check table structure
echo "<h3>1. Table Structure</h3>\n";
$result = $conn->query("SHOW COLUMNS FROM invite_codes");
if ($result) {
    echo "<table border='1' cellpadding='5'>\n";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>\n";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
} else {
    echo "<p style='color: red;'>Error: " . $conn->error . "</p>\n";
}

// Check current data
echo "<h3>2. Current Data</h3>\n";
$result = $conn->query("SELECT * FROM invite_codes ORDER BY created_at DESC");
if ($result) {
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>\n";
        echo "<tr><th>ID</th><th>Code</th><th>User Type</th><th>Email</th><th>Room Code</th><th>Created By</th><th>Created At</th><th>Expires At</th><th>Is Used</th><th>Used At</th></tr>\n";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['code'] . "</td>";
            echo "<td>" . $row['user_type'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . ($row['room_code'] ?? '-') . "</td>";
            echo "<td>" . $row['created_by'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "<td>" . $row['expires_at'] . "</td>";
            echo "<td style='background-color: " . ($row['is_used'] ? '#ffcccc' : '#ccffcc') . ";'>" . $row['is_used'] . "</td>";
            echo "<td>" . ($row['used_at'] ?? '-') . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<p>No invite codes found in the database.</p>\n";
    }
} else {
    echo "<p style='color: red;'>Error: " . $conn->error . "</p>\n";
}

// Test creating a new invite code
echo "<h3>3. Test Creating New Invite Code</h3>\n";
$testCode = 'TEST' . strtoupper(bin2hex(random_bytes(4)));
$testEmail = 'test@example.com';
$expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

echo "<p>Creating test invite code: <strong>$testCode</strong></p>\n";

$sql = "INSERT INTO invite_codes (code, user_type, email, room_code, created_by, expires_at) VALUES (?, 'resident', ?, '12-34-A5', 1, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $testCode, $testEmail, $expiresAt);

if ($stmt->execute()) {
    echo "<p style='color: green;'>✓ Test invite code created successfully</p>\n";
    
    // Check what was actually inserted
    $checkResult = $conn->query("SELECT * FROM invite_codes WHERE code = '$testCode'");
    if ($checkResult && $checkResult->num_rows > 0) {
        $row = $checkResult->fetch_assoc();
        echo "<p>Inserted data:</p>\n";
        echo "<ul>\n";
        echo "<li>ID: " . $row['id'] . "</li>\n";
        echo "<li>Code: " . $row['code'] . "</li>\n";
        echo "<li>Is Used: " . $row['is_used'] . "</li>\n";
        echo "<li>Used At: " . ($row['used_at'] ?? 'NULL') . "</li>\n";
        echo "</ul>\n";
    }
    
    // Clean up test data
    $conn->query("DELETE FROM invite_codes WHERE code = '$testCode'");
    echo "<p>Test data cleaned up.</p>\n";
} else {
    echo "<p style='color: red;'>✗ Failed to create test invite code: " . $stmt->error . "</p>\n";
}

$conn->close();
?>
