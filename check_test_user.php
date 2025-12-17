<?php
session_start();
include 'db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

echo "<h2>Test User Check</h2>";

// Check for variations of the email
$emails = ['ian@pharmacy.com', 'Ian@pharmacy.com', 'IAN@PHARMACY.COM'];

foreach ($emails as $email) {
    echo "<h3>Checking: " . $email . "</h3>";
    $stmt = $conn->prepare("SELECT id, name, email, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        echo "<ul>";
        echo "<li>ID: " . $user['id'] . "</li>";
        echo "<li>Name: " . htmlspecialchars($user['name']) . "</li>";
        echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
        echo "<li>Role: " . $user['role'] . "</li>";
        echo "<li>Status: " . $user['status'] . "</li>";
        echo "</ul>";
    } else {
        echo "<p>Not found</p>";
    }
    $stmt->close();
}

$conn->close();

echo "<p><a href='admin_approvals.php'>Back to Admin Approvals</a></p>";
?>