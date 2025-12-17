<?php
include 'db.php';

echo "<h1>MyPharma Approval System Test</h1>";

// Test 1: Check if status column exists
echo "<h2>Test 1: Status Column Check</h2>";
$checkQuery = "SHOW COLUMNS FROM users LIKE 'status'";
$checkResult = $conn->query($checkQuery);

if ($checkResult && $checkResult->num_rows > 0) {
    echo "<p style='color: green;'>✓ Status column exists in users table</p>";
} else {
    echo "<p style='color: red;'>✗ Status column does not exist in users table</p>";
}

// Test 2: Check pending users
echo "<h2>Test 2: Pending Users Check</h2>";
$stmt = $conn->prepare("SELECT id, name, email, role, status FROM users WHERE status = 'pending'");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<p style='color: orange;'>⚠ Found " . $result->num_rows . " pending users:</p>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>";
    
    while ($user = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "<td>" . $user['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: green;'>✓ No pending users found</p>";
}
$stmt->close();

// Test 3: Check approved users
echo "<h2>Test 3: Approved Users Check</h2>";
$stmt = $conn->prepare("SELECT id, name, email, role, status FROM users WHERE status = 'approved'");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Found " . $result->num_rows . " approved users:</p>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>";
    
    while ($user = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "<td>" . $user['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠ No approved users found</p>";
}
$stmt->close();

$conn->close();

echo "<p><a href='admin_dashboard.php'>Back to Admin Dashboard</a></p>";
?>