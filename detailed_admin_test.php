<?php
session_start();
include 'db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

echo "<h2>Detailed Admin Approval Test</h2>";

// Check current status of ian@pharmacy.com
echo "<h3>Current Status Check for ian@pharmacy.com:</h3>";
$stmt = $conn->prepare("SELECT id, name, email, role, status FROM users WHERE email = 'ian@pharmacy.com'");
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    echo "<p><strong>Before Update:</strong></p>";
    echo "<ul>";
    echo "<li>ID: " . $user['id'] . "</li>";
    echo "<li>Name: " . htmlspecialchars($user['name']) . "</li>";
    echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
    echo "<li>Role: " . $user['role'] . "</li>";
    echo "<li>Status: " . $user['status'] . "</li>";
    echo "</ul>";
    
    // Try to update the user
    echo "<h3>Attempting Update:</h3>";
    $updateStmt = $conn->prepare("UPDATE users SET status = 'approved', role = 'staff' WHERE email = 'ian@pharmacy.com'");
    if ($updateStmt->execute()) {
        echo "<p>Affected rows: " . $updateStmt->affected_rows . "</p>";
        if ($updateStmt->affected_rows > 0) {
            echo "<p style='color: green;'>Update query executed successfully!</p>";
        } else {
            echo "<p style='color: orange;'>Update query executed but no rows were affected.</p>";
        }
    } else {
        echo "<p style='color: red;'>Update query failed: " . $conn->error . "</p>";
    }
    $updateStmt->close();
    
    // Check status after update
    echo "<h3>Status After Update:</h3>";
    $stmt2 = $conn->prepare("SELECT id, name, email, role, status FROM users WHERE email = 'ian@pharmacy.com'");
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
    if ($user2 = $result2->fetch_assoc()) {
        echo "<p><strong>After Update:</strong></p>";
        echo "<ul>";
        echo "<li>ID: " . $user2['id'] . "</li>";
        echo "<li>Name: " . htmlspecialchars($user2['name']) . "</li>";
        echo "<li>Email: " . htmlspecialchars($user2['email']) . "</li>";
        echo "<li>Role: " . $user2['role'] . "</li>";
        echo "<li>Status: " . $user2['status'] . "</li>";
        echo "</ul>";
    }
    $stmt2->close();
    
} else {
    echo "<p style='color: red;'>User ian@pharmacy.com not found!</p>";
}
$stmt->close();

$conn->close();

echo "<p><a href='admin_approvals.php'>Back to Admin Approvals</a></p>";
?>