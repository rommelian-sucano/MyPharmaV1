<?php
include 'db.php';

echo "<h2>Minimal Diagnostic Test</h2>";
echo "<p>If you can see this, the file is working.</p>";

// Test pending user query with status column
echo "<h3>Testing Pending User Query with Status Column</h3>";
$result = $conn->query("SELECT id, name, email, role, status FROM users WHERE status = 'pending' ORDER BY id DESC");

if ($result && $result->num_rows > 0) {
    echo "<p>Found " . $result->num_rows . " pending users:</p>";
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
    echo "<p>No pending users found.</p>";
}

$conn->close();

echo "<p><a href='admin_dashboard.php'>Back to Admin Dashboard</a></p>";