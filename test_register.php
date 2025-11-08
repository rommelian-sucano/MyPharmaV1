<?php
include 'db.php';

// Test registration functionality
$name = "Test Staff Member";
$email = "teststaff@example.com";
$password = "123456";
$confirm_password = "123456";

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert user into database with 'pending' role
$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'pending')");
$stmt->bind_param("sss", $name, $email, $hashed_password);

if ($stmt->execute()) {
    echo "Test registration successful! User ID: " . $conn->insert_id;
} else {
    echo "Test registration failed: " . $stmt->error;
}
$stmt->close();

$conn->close();
?>