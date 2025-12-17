<?php
session_start();
include 'db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

echo "<h2>Comprehensive Approval Debug</h2>";

// Check a specific user
if (isset($_GET['email'])) {
    $email = $_GET['email'];
    
    echo "<h3>Debugging User: " . htmlspecialchars($email) . "</h3>";
    
    // Check current database status
    $stmt = $conn->prepare("SELECT id, name, email, role, status, created_at FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        echo "<h4>Current Database Status:</h4>";
        echo "<ul>";
        echo "<li>ID: " . $user['id'] . "</li>";
        echo "<li>Name: " . htmlspecialchars($user['name']) . "</li>";
        echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
        echo "<li>Role: " . $user['role'] . "</li>";
        echo "<li>Status: " . $user['status'] . "</li>";
        echo "<li>Created: " . $user['created_at'] . "</li>";
        echo "</ul>";
        
        // Try to approve
        if (isset($_GET['approve'])) {
            echo "<h4>Attempting Approval:</h4>";
            $updateStmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE email = ?");
            $updateStmt->bind_param("s", $email);
            
            if ($updateStmt->execute()) {
                echo "<p>Affected rows: " . $updateStmt->affected_rows . "</p>";
                if ($updateStmt->affected_rows > 0) {
                    echo "<p style='color: green;'>Approval query executed successfully!</p>";
                } else {
                    echo "<p style='color: orange;'>Approval query executed but no rows affected.</p>";
                }
            } else {
                echo "<p style='color: red;'>Approval query failed: " . $conn->error . "</p>";
            }
            $updateStmt->close();
            
            // Verify after update
            echo "<h4>Verification After Update:</h4>";
            $verifyStmt = $conn->prepare("SELECT status FROM users WHERE email = ?");
            $verifyStmt->bind_param("s", $email);
            $verifyStmt->execute();
            $verifyResult = $verifyStmt->get_result();
            
            if ($verifyUser = $verifyResult->fetch_assoc()) {
                echo "<p>Verified Status: " . $verifyUser['status'] . "</p>";
                if ($verifyUser['status'] == 'approved') {
                    echo "<p style='color: green;'>✓ Status is now approved in database!</p>";
                } else {
                    echo "<p style='color: red;'>✗ Status is still not approved!</p>";
                }
            }
            $verifyStmt->close();
        }
        
        echo "<p><a href='?email=" . urlencode($email) . "&approve=1' class='btn btn-primary'>Try to Approve This User</a></p>";
    } else {
        echo "<p style='color: red;'>User not found!</p>";
    }
    $stmt->close();
}

// Form to check specific user
echo "<h3>Check Specific User</h3>";
echo "<form method='GET'>";
echo "<div class='mb-3'>";
echo "<label for='email' class='form-label'>Email Address:</label>";
echo "<input type='email' class='form-control' id='email' name='email' required>";
echo "</div>";
echo "<button type='submit' class='btn btn-primary'>Check User</button>";
echo "</form>";

$conn->close();

echo "<p><a href='admin_approvals.php'>Back to Admin Approvals</a></p>";
?>