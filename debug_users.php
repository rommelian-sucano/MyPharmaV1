<?php
include 'db.php';

echo "<h2>Debug: All Users in Database</h2>";

// Get all users
$stmt = $conn->prepare("SELECT id, name, email, role, created_at FROM users ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created At</th></tr>";
    
    while ($user = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "<td>" . $user['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found in the database.</p>";
}

$stmt->close();
$conn->close();
?>