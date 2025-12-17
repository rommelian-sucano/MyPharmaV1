<?php
// Simple approval script without session checks
include 'db.php';

echo "<h2>Simple User Approval</h2>";

// Approve user directly
$email = "zxcv@gmail.com";
$stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE email = ?");
$stmt->bind_param("s", $email);

if ($stmt->execute()) {
    echo "<p>Query executed. Affected rows: " . $stmt->affected_rows . "</p>";
    if ($stmt->affected_rows > 0) {
        echo "<p style='color: green;'>User " . $email . " has been approved!</p>";
    } else {
        echo "<p>No rows were updated. Check if the email exists.</p>";
    }
} else {
    echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
}
$stmt->close();

// Verify the update
$checkStmt = $conn->prepare("SELECT id, name, email, role, status FROM users WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($user = $result->fetch_assoc()) {
    echo "<h3>Verification:</h3>";
    echo "<ul>";
    echo "<li>Name: " . htmlspecialchars($user['name']) . "</li>";
    echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
    echo "<li>Role: " . $user['role'] . "</li>";
    echo "<li>Status: " . $user['status'] . "</li>";
    echo "</ul>";
} else {
    echo "<p>User not found after update!</p>";
}
$checkStmt->close();

$conn->close();

echo "<p><a href='admin_approvals.php'>Back to Admin Approvals</a></p>";
?>