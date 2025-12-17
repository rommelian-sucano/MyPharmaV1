<?php
include 'db.php';

echo "<h1>Database Update for Approval System</h1>";

// Check if connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "<p style='color: green;'>✓ Database connection successful</p>";

// Check if status column already exists
echo "<h2>Checking for status column...</h2>";
$checkQuery = "SHOW COLUMNS FROM users LIKE 'status'";
$checkResult = $conn->query($checkQuery);

if ($checkResult && $checkResult->num_rows > 0) {
    echo "<p style='color: green;'>✓ Status column already exists in users table.</p>";
} else {
    echo "<p style='color: orange;'>Status column not found. Adding it now...</p>";
    
    // Add status column
    $addColumnQuery = "ALTER TABLE users ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'";
    
    if ($conn->query($addColumnQuery) === TRUE) {
        echo "<p style='color: green;'>✓ Status column added successfully!</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding status column: " . $conn->error . "</p>";
        $conn->close();
        exit;
    }
}

// Update existing users to have proper status values
echo "<h2>Updating existing users...</h2>";
$updateQuery = "UPDATE users SET status = CASE 
    WHEN role = 'pending' THEN 'pending'
    WHEN role IN ('user', 'staff', 'admin') THEN 'approved'
    ELSE 'pending'
END";

if ($conn->query($updateQuery) === TRUE) {
    echo "<p style='color: green;'>✓ Users updated successfully!</p>";
    echo "<p>Rows affected: " . $conn->affected_rows . "</p>";
} else {
    echo "<p style='color: red;'>✗ Error updating users: " . $conn->error . "</p>";
}

// Show sample of users with their status
echo "<h2>Sample users with status:</h2>";
$sampleQuery = "SELECT id, name, email, role, status FROM users ORDER BY id LIMIT 10";
$sampleResult = $conn->query($sampleQuery);

if ($sampleResult && $sampleResult->num_rows > 0) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>";
    while ($row = $sampleResult->fetch_assoc()) {
        echo "<tr>
            <td>" . $row['id'] . "</td>
            <td>" . htmlspecialchars($row['name']) . "</td>
            <td>" . htmlspecialchars($row['email']) . "</td>
            <td>" . $row['role'] . "</td>
            <td>" . $row['status'] . "</td>
        </tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Error fetching sample users: " . $conn->error . "</p>";
}

$conn->close();

echo "<p><a href='admin_dashboard.php'>Back to Admin Dashboard</a></p>";
echo "<p><a href='simple_diagnostic.php'>Run Diagnostic Again</a></p>";
?>