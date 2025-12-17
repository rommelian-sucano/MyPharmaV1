<?php
session_start();
include 'db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

echo "<h2>Admin Approval Test</h2>";

// Get all pending users
$stmt = $conn->prepare("SELECT id, name, email, role, pharmacy_name, status FROM users WHERE status = 'pending' ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<h3>Pending Users for Approval:</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Current Role</th><th>User Type</th><th>Actions</th></tr>";
    
    while ($user = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "<td>" . (!empty($user['pharmacy_name']) ? 'Pharmacy Owner' : 'Staff Member') . "</td>";
        echo "<td>";
        echo "<form method='POST' style='display:inline;'>";
        echo "<input type='hidden' name='user_id' value='" . $user['id'] . "'>";
        echo "<button type='submit' name='test_approve'>Test Approve</button>";
        echo "</form>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No pending users found.</p>";
}
$stmt->close();

// Handle test approval
if (isset($_POST['test_approve'])) {
    $user_id = $_POST['user_id'];
    
    echo "<h3>Approval Process Debug:</h3>";
    
    // Get user details
    $getUserStmt = $conn->prepare("SELECT role, pharmacy_name, status FROM users WHERE id = ?");
    $getUserStmt->bind_param("i", $user_id);
    $getUserStmt->execute();
    $getUserResult = $getUserStmt->get_result();
    
    if ($user = $getUserResult->fetch_assoc()) {
        echo "<p>Current User Data:</p>";
        echo "<ul>";
        echo "<li>Role: " . $user['role'] . "</li>";
        echo "<li>Pharmacy Name: " . ($user['pharmacy_name'] ? $user['pharmacy_name'] : 'None') . "</li>";
        echo "<li>Status: " . $user['status'] . "</li>";
        echo "</ul>";
        
        // Determine the appropriate role
        $new_role = (!empty($user['pharmacy_name'])) ? 'owner' : 'staff';
        echo "<p>Determined Role: " . $new_role . "</p>";
        
        // Update user
        $updateStmt = $conn->prepare("UPDATE users SET status = 'approved', role = ? WHERE id = ?");
        $updateStmt->bind_param("si", $new_role, $user_id);
        
        if ($updateStmt->execute()) {
            echo "<p style='color: green;'>User approved successfully!</p>";
            echo "<p>New Role: " . $new_role . "</p>";
            echo "<p>New Status: approved</p>";
        } else {
            echo "<p style='color: red;'>Error approving user: " . $conn->error . "</p>";
        }
        $updateStmt->close();
    } else {
        echo "<p style='color: red;'>User not found!</p>";
    }
    $getUserStmt->close();
    
    echo "<p><a href='?'>Refresh List</a></p>";
}

$conn->close();
?>