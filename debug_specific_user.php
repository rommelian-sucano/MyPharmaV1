<?php
include 'db.php';

echo "<h1>Specific User Debug</h1>";

// Check specific user
$user_id = 106;
$stmt = $conn->prepare("SELECT id, name, email, role, status FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    echo "<h3>User Details (ID: " . $user['id'] . "):</h3>";
    echo "<ul>";
    echo "<li>Name: " . htmlspecialchars($user['name']) . "</li>";
    echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
    echo "<li>Role: " . $user['role'] . "</li>";
    echo "<li>Status: " . $user['status'] . "</li>";
    echo "</ul>";
} else {
    echo "<p>User with ID " . $user_id . " not found.</p>";
}
$stmt->close();

// Also check by email
$email = 'ian@pharmacy.com';
$stmt2 = $conn->prepare("SELECT id, name, email, role, status FROM users WHERE email = ?");
$stmt2->bind_param("s", $email);
$stmt2->execute();
$result2 = $stmt2->get_result();

if ($user2 = $result2->fetch_assoc()) {
    echo "<h3>User Details (Email: " . $email . "):</h3>";
    echo "<ul>";
    echo "<li>ID: " . $user2['id'] . "</li>";
    echo "<li>Name: " . htmlspecialchars($user2['name']) . "</li>";
    echo "<li>Email: " . htmlspecialchars($user2['email']) . "</li>";
    echo "<li>Role: " . $user2['role'] . "</li>";
    echo "<li>Status: " . $user2['status'] . "</li>";
    echo "</ul>";
} else {
    echo "<p>User with email " . $email . " not found.</p>";
}
$stmt2->close();

$conn->close();

echo "<p><a href='admin_dashboard.php'>Back to Admin Dashboard</a></p>";
?>