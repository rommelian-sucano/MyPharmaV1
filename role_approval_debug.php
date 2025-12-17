<?php
session_start();
include 'db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

echo "<h2>Role Approval Debug</h2>";

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    
    echo "<h3>Analyzing User ID: " . $user_id . "</h3>";
    
    // Get user details before approval
    $getUserStmt = $conn->prepare("SELECT id, name, email, role, pharmacy_name, status FROM users WHERE id = ?");
    $getUserStmt->bind_param("i", $user_id);
    $getUserStmt->execute();
    $getUserResult = $getUserStmt->get_result();
    
    if ($user = $getUserResult->fetch_assoc()) {
        echo "<h4>Before Approval:</h4>";
        echo "<ul>";
        echo "<li>Name: " . htmlspecialchars($user['name']) . "</li>";
        echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
        echo "<li>Current Role: " . $user['role'] . "</li>";
        echo "<li>Pharmacy Name: " . ($user['pharmacy_name'] ? $user['pharmacy_name'] : 'None') . "</li>";
        echo "<li>Current Status: " . $user['status'] . "</li>";
        echo "</ul>";
        
        // Determine what role would be assigned
        $new_role = (!empty($user['pharmacy_name'])) ? 'owner' : 'staff';
        echo "<p><strong>System would assign role:</strong> " . $new_role . "</p>";
        
        // Simulate the approval process
        if (isset($_GET['approve'])) {
            echo "<h4>Simulating Approval Process:</h4>";
            
            // Update user status and role
            $stmt = $conn->prepare("UPDATE users SET status = 'approved', role = ? WHERE id = ?");
            $stmt->bind_param("si", $new_role, $user_id);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>Update executed successfully!</p>";
                echo "<p>Affected rows: " . $stmt->affected_rows . "</p>";
            } else {
                echo "<p style='color: red;'>Update failed: " . $conn->error . "</p>";
            }
            $stmt->close();
            
            // Verify after update
            echo "<h4>After Update:</h4>";
            $verifyStmt = $conn->prepare("SELECT role, status FROM users WHERE id = ?");
            $verifyStmt->bind_param("i", $user_id);
            $verifyStmt->execute();
            $verifyResult = $verifyStmt->get_result();
            
            if ($verifyUser = $verifyResult->fetch_assoc()) {
                echo "<ul>";
                echo "<li>Role: " . $verifyUser['role'] . "</li>";
                echo "<li>Status: " . $verifyUser['status'] . "</li>";
                echo "</ul>";
                
                if ($verifyUser['role'] == $new_role && $verifyUser['status'] == 'approved') {
                    echo "<p style='color: green;'>✓ Role and status updated correctly!</p>";
                } else {
                    echo "<p style='color: red;'>✗ Role or status not updated correctly!</p>";
                }
            }
            $verifyStmt->close();
        }
        
        echo "<p><a href='?user_id=" . $user_id . "&approve=1' class='btn btn-primary'>Simulate Approval</a></p>";
    } else {
        echo "<p style='color: red;'>User not found!</p>";
    }
    $getUserStmt->close();
}

// Show all pending users
echo "<h3>Pending Users:</h3>";
$stmt = $conn->prepare("SELECT id, name, email, role, pharmacy_name FROM users WHERE status = 'pending' ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Current Role</th><th>User Type</th><th>Action</th></tr>";
    
    while ($user = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "<td>" . (!empty($user['pharmacy_name']) ? 'Pharmacy Owner' : 'Staff Member') . "</td>";
        echo "<td><a href='?user_id=" . $user['id'] . "'>Analyze</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No pending users found.</p>";
}
$stmt->close();

$conn->close();

echo "<p><a href='admin_approvals.php'>Back to Admin Approvals</a></p>";
?>