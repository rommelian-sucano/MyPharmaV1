<?php
include 'db.php';

// Check if test user was added
$email = "teststaff@example.com";
$stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "Test user found:\n";
    echo "ID: " . $user['id'] . "\n";
    echo "Name: " . $user['name'] . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "Role: " . $user['role'] . "\n";
} else {
    echo "Test user not found in database.\n";
}

$stmt->close();
$conn->close();
?>