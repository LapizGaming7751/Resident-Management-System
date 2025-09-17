<?php
// Test script for the invite code system
// This script tests the database schema and basic functionality

$host = 'localhost';
$db = 'finals_scanner';
$db_user = 'root';
$db_pass = '';

$conn = new mysqli($host, $db_user, $db_pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

echo "<h2>Testing Invite Code System</h2>\n";

// Test 1: Check if new tables exist
echo "<h3>1. Checking Database Tables</h3>\n";
$tables = ['residents', 'security', 'invite_codes'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✓ Table '$table' exists<br>\n";
        
        // Check columns
        $columns = $conn->query("SHOW COLUMNS FROM $table");
        echo "  Columns: ";
        $columnNames = [];
        while ($row = $columns->fetch_assoc()) {
            $columnNames[] = $row['Field'];
        }
        echo implode(', ', $columnNames) . "<br>\n";
    } else {
        echo "✗ Table '$table' does not exist<br>\n";
    }
}

// Test 2: Check if residents table has email column
echo "<h3>2. Checking Residents Table Schema</h3>\n";
$result = $conn->query("SHOW COLUMNS FROM residents");
$hasEmail = false;
$hasIsActive = false;
while ($row = $result->fetch_assoc()) {
    if ($row['Field'] === 'email') $hasEmail = true;
    if ($row['Field'] === 'is_active') $hasIsActive = true;
}

echo $hasEmail ? "✓ Email column exists in residents table<br>\n" : "✗ Email column missing in residents table<br>\n";
echo $hasIsActive ? "✓ is_active column exists in residents table<br>\n" : "✗ is_active column missing in residents table<br>\n";

// Test 3: Check if security table has email column
echo "<h3>3. Checking Security Table Schema</h3>\n";
$result = $conn->query("SHOW COLUMNS FROM security");
$hasEmail = false;
$hasIsActive = false;
while ($row = $result->fetch_assoc()) {
    if ($row['Field'] === 'email') $hasEmail = true;
    if ($row['Field'] === 'is_active') $hasIsActive = true;
}

echo $hasEmail ? "✓ Email column exists in security table<br>\n" : "✗ Email column missing in security table<br>\n";
echo $hasIsActive ? "✓ is_active column exists in security table<br>\n" : "✗ is_active column missing in security table<br>\n";

// Test 4: Test invite_codes table structure
echo "<h3>4. Checking Invite Codes Table</h3>\n";
$result = $conn->query("SHOW COLUMNS FROM invite_codes");
$requiredColumns = ['id', 'code', 'user_type', 'email', 'room_code', 'created_by', 'created_at', 'expires_at', 'is_used', 'used_at'];
$existingColumns = [];
while ($row = $result->fetch_assoc()) {
    $existingColumns[] = $row['Field'];
}

foreach ($requiredColumns as $col) {
    echo in_array($col, $existingColumns) ? "✓ Column '$col' exists<br>\n" : "✗ Column '$col' missing<br>\n";
}

// Test 5: Test creating a sample invite code
echo "<h3>5. Testing Invite Code Creation</h3>\n";
$testCode = 'TEST' . strtoupper(bin2hex(random_bytes(4)));
$testEmail = 'test@example.com';
$expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

$sql = "INSERT INTO invite_codes (code, user_type, email, room_code, created_by, expires_at) VALUES (?, 'resident', ?, '12-34-A5', 1, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $testCode, $testEmail, $expiresAt);

if ($stmt->execute()) {
    echo "✓ Successfully created test invite code: $testCode<br>\n";
    
    // Clean up test data
    $conn->query("DELETE FROM invite_codes WHERE code = '$testCode'");
    echo "✓ Test data cleaned up<br>\n";
} else {
    echo "✗ Failed to create test invite code: " . $conn->error . "<br>\n";
}

echo "<h3>Test Complete!</h3>\n";
echo "<p>If all tests show ✓, the invite code system is ready to use.</p>\n";
echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ul>\n";
echo "<li>Run the updated SQL schema on your database</li>\n";
echo "<li>Test creating invite codes through the admin interface</li>\n";
echo "<li>Test registration with invite codes</li>\n";
echo "</ul>\n";

$conn->close();
?>
