<?php
include 'db.php';

echo "<h2>Updating Existing Users Status</h2>";

// Update existing users to have proper status values based on their role
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
?>