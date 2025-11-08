<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check if id parameter is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$user_id = $_GET['id'];

// Update user status to rejected
$stmt = $conn->prepare("UPDATE users SET status = 'rejected' WHERE id = ? AND status = 'pending'");
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $_SESSION['message'] = "User rejected successfully.";
    } else {
        $_SESSION['error'] = "User not found or already processed.";
    }
} else {
    $_SESSION['error'] = "Error rejecting user.";
}
$stmt->close();

header("Location: admin_dashboard.php");
exit();
?>