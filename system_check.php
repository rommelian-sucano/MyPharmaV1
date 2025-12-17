<?php
include 'db.php';

echo "<h1>MyPharma System Check</h1>";

// Test database connection
if ($conn->connect_error) {
    die("<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>");
}
echo "<p style='color: green;'>✓ Database connection successful</p>";

// Check if users table exists
$usersTable = $conn->query("SHOW TABLES LIKE 'users'");
if ($usersTable && $usersTable->num_rows > 0) {
    echo "<p style='color: green;'>✓ Users table exists</p>";
    
    // Check if status column exists
    $statusColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
    if ($statusColumn && $statusColumn->num_rows > 0) {
        echo "<p style='color: green;'>✓ Status column exists</p>";
        
        // Count pending users
        $pendingCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'pending'");
        if ($pendingCount && $row = $pendingCount->fetch_assoc()) {
            echo "<p style='color: blue;'>Pending users: " . $row['count'] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Status column does not exist</p>";
        echo "<p>Please run <a href='update_status_column.php'>update_status_column.php</a></p>";
    }
    
    // Show sample users
    $sampleUsers = $conn->query("SELECT id, name, email, role, status FROM users LIMIT 5");
    if ($sampleUsers && $sampleUsers->num_rows > 0) {
        echo "<h3>Sample Users:</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>";
        while ($user = $sampleUsers->fetch_assoc()) {
            echo "<tr>
                <td>" . $user['id'] . "</td>
                <td>" . htmlspecialchars($user['name']) . "</td>
                <td>" . htmlspecialchars($user['email']) . "</td>
                <td>" . $user['role'] . "</td>
                <td>" . $user['status'] . "</td>
            </tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: red;'>✗ Users table does not exist</p>";
}

$conn->close();

echo "<p><a href='admin_dashboard.php'>Back to Admin Dashboard</a></p>";
?>