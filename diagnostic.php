<?php
include 'db.php';

echo "<h1>MyPharma Simple Diagnostic</h1>";
echo "<p>This is a simple test to see if PHP files are working.</p>";

// Test database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "<p style='color: green;'>✓ Database connection successful</p>";

// Check if status column exists
$checkQuery = "SHOW COLUMNS FROM users LIKE 'status'";
$checkResult = $conn->query($checkQuery);

if ($checkResult && $checkResult->num_rows > 0) {
    echo "<p style='color: green;'>✓ Status column exists</p>";
} else {
    echo "<p style='color: red;'>✗ Status column does not exist</p>";
}

// Show some user data
$stmt = $conn->prepare("SELECT id, name, email, role FROM users LIMIT 5");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<h3>Sample Users:</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
    
    while ($user = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$stmt->close();
$conn->close();

echo "<p><a href='admin_dashboard.php'>Back to Admin Dashboard</a></p>";
?>