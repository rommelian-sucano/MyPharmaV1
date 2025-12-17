<?php
include 'db.php';

echo "<h2>Direct Login Test</h2>";

// Test the exact query used in login.php
$email = 'zxcv@gmail.com';
$stmt = $conn->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    echo "<h3>User Data from Database:</h3>";
    echo "<ul>";
    echo "<li>ID: " . $user['id'] . "</li>";
    echo "<li>Name: " . htmlspecialchars($user['name']) . "</li>";
    echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
    echo "<li>Role: " . $user['role'] . "</li>";
    echo "<li>Status: " . $user['status'] . "</li>";
    echo "<li>Status Length: " . strlen($user['status']) . " characters</li>";
    echo "<li>Status Hex: " . bin2hex($user['status']) . "</li>"; // This will show any hidden characters
    echo "</ul>";
    
    echo "<h3>Login Decision:</h3>";
    if ($user['status'] != 'approved') {
        echo "<p style='color: red;'>✗ Would be blocked: Status is not 'approved'</p>";
        echo "<p>Current status value: '" . $user['status'] . "'</p>";
        echo "<p>Comparison result: " . ($user['status'] != 'approved' ? "TRUE (different)" : "FALSE (same)") . "</p>";
    } else {
        echo "<p style='color: green;'>✓ Would be allowed: Status is 'approved'</p>";
    }
} else {
    echo "<p style='color: red;'>User not found!</p>";
}
$stmt->close();

$conn->close();

echo "<p><a href='login.php'>Back to Login</a></p>";
?>