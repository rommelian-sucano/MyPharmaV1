<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$email = 'meow@pharmacy.com';

echo "<h2>Manually Approving User: $email</h2>";

// First, get the user ID
$getUserStmt = $conn->prepare("SELECT id, name, role, status FROM users WHERE email = ?");
$getUserStmt->bind_param("s", $email);
$getUserStmt->execute();
$getUserResult = $getUserStmt->get_result();

if ($user = $getUserResult->fetch_assoc()) {
    echo "<p>Current Status - ID: " . $user['id'] . ", Name: " . htmlspecialchars($user['name']) . ", Role: " . $user['role'] . ", Status: " . $user['status'] . "</p>";
    
    $user_id = $user['id'];
    
    // Update user status to approved
    $stmt = $conn->prepare("UPDATE users SET status = 'approved', role = CASE WHEN role = 'pending' THEN 'staff' ELSE role END WHERE id = ? AND status = 'pending'");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        echo "<p>Affected rows: " . $stmt->affected_rows . "</p>";
        
        if ($stmt->affected_rows > 0) {
            echo "<p style='color: green;'>User approved successfully!</p>";
        } else {
            echo "<p style='color: orange;'>User not found or already processed.</p>";
        }
    } else {
        echo "<p style='color: red;'>Error approving user: " . $conn->error . "</p>";
    }
    
    $stmt->close();
    
    // Check the user's current status after approval
    $checkStmt = $conn->prepare("SELECT id, name, email, role, status FROM users WHERE id = ?");
    $checkStmt->bind_param("i", $user_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($row = $checkResult->fetch_assoc()) {
        echo "<h3>User Status After Approval Attempt:</h3>";
        echo "<p>ID: " . $row['id'] . "</p>";
        echo "<p>Name: " . htmlspecialchars($row['name']) . "</p>";
        echo "<p>Email: " . htmlspecialchars($row['email']) . "</p>";
        echo "<p>Role: " . $row['role'] . "</p>";
        echo "<p>Status: " . $row['status'] . "</p>";
        
        if ($row['status'] == 'approved') {
            echo "<p style='color: green;'>✓ User is now properly approved!</p>";
        } else {
            echo "<p style='color: red;'>✗ User is still not approved.</p>";
        }
    }
    
    $checkStmt->close();
} else {
    echo "<p style='color: red;'>User with email $email not found!</p>";
}

$getUserStmt->close();
$conn->close();

echo "<p><a href='admin_approvals.php'>Back to Admin Approvals</a></p>";
?>