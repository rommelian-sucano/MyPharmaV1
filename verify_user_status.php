<?php
include 'db.php';

echo "<h2>User Status Verification</h2>";

if (isset($_GET['email'])) {
    $email = $_GET['email'];
    
    echo "<h3>Checking User: " . htmlspecialchars($email) . "</h3>";
    
    // Check user status
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
        
        if ($user['status'] == 'approved') {
            echo "<p style='color: green; font-weight: bold;'>✓ This user IS approved and should be able to log in!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>✗ This user is NOT approved (status: " . $user['status'] . ")</p>";
        }
    } else {
        echo "<p style='color: red;'>User not found in database!</p>";
    }
    $stmt->close();
}

// Form to check user
echo "<h3>Check User Status</h3>";
echo "<form method='GET'>";
echo "<div class='mb-3'>";
echo "<label for='email' class='form-label'>Email Address:</label>";
echo "<input type='email' class='form-control' id='email' name='email' required>";
echo "</div>";
echo "<button type='submit' class='btn btn-primary'>Check Status</button>";
echo "</form>";

$conn->close();

echo "<p><a href='login.php'>Back to Login</a></p>";
?>