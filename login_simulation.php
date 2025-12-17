<?php
include 'db.php';

echo "<h2>Login Process Simulation</h2>";

if (isset($_GET['email'])) {
    $email = $_GET['email'];
    $password = $_GET['password'] ?? '';
    
    echo "<h3>Simulating Login for: " . htmlspecialchars($email) . "</h3>";
    
    // Exactly replicate the login process
    $stmt = $conn->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        echo "<h4>Database User Data:</h4>";
        echo "<ul>";
        echo "<li>ID: " . $user['id'] . "</li>";
        echo "<li>Name: " . htmlspecialchars($user['name']) . "</li>";
        echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
        echo "<li>Role: " . $user['role'] . "</li>";
        echo "<li>Status: " . $user['status'] . "</li>";
        echo "<li>Status Length: " . strlen($user['status']) . " characters</li>";
        echo "<li>Status Hex: " . bin2hex($user['status']) . "</li>";
        echo "</ul>";
        
        // Check approval status (exactly like login.php)
        echo "<h4>Login Decision Process:</h4>";
        echo "<p>Checking if status != 'approved':</p>";
        
        if ($user['status'] != 'approved') {
            echo "<p style='color: red;'>✗ BLOCKED: User status is '" . $user['status'] . "' which is != 'approved'</p>";
            echo "<p>Would show message: 'Your account is pending approval by an administrator. Please wait for approval before logging in.'</p>";
        } else {
            echo "<p style='color: green;'>✓ ALLOWED: User status is '" . $user['status'] . "' which is == 'approved'</p>";
            echo "<p>Would proceed to dashboard</p>";
        }
        
        // If password was provided, also test password verification
        if (!empty($password)) {
            echo "<h4>Password Verification:</h4>";
            if (password_verify($password, $user['password'])) {
                echo "<p style='color: green;'>✓ Password is correct</p>";
            } else {
                echo "<p style='color: red;'>✗ Password is incorrect</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>User not found in database!</p>";
    }
    $stmt->close();
}

// Form to test login
echo "<h3>Test Login Process</h3>";
echo "<form method='GET'>";
echo "<div class='mb-3'>";
echo "<label for='email' class='form-label'>Email Address:</label>";
echo "<input type='email' class='form-control' id='email' name='email' required>";
echo "</div>";
echo "<div class='mb-3'>";
echo "<label for='password' class='form-label'>Password (optional for testing):</label>";
echo "<input type='password' class='form-control' id='password' name='password'>";
echo "</div>";
echo "<button type='submit' class='btn btn-primary'>Simulate Login</button>";
echo "</form>";

$conn->close();

echo "<p><a href='login.php'>Back to Login</a></p>";
?>