<?php
include 'db.php';

echo "<h2>Database Status Update</h2>";

// Check if status column exists
$checkQuery = "SHOW COLUMNS FROM users LIKE 'status'";
$checkResult = $conn->query($checkQuery);

if ($checkResult && $checkResult->num_rows > 0) {
    echo "<p style='color: green;'>✓ Status column already exists</p>";
} else {
    echo "<p style='color: red;'>✗ Status column does not exist - adding it now...</p>";
    
    // Add status column
    $addColumnQuery = "ALTER TABLE users ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'";
    
    if ($conn->query($addColumnQuery) === TRUE) {
        echo "<p style='color: green;'>✓ Status column added successfully!</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding status column: " . $conn->error . "</p>";
    }
}

// Check if pharmacy columns exist
$pharmacyColumns = ['pharmacy_name', 'pharmacy_address', 'pharmacy_lat', 'pharmacy_lng', 'pharmacy_contact'];
foreach ($pharmacyColumns as $column) {
    $checkQuery = "SHOW COLUMNS FROM users LIKE '$column'";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        echo "<p style='color: green;'>✓ $column column exists</p>";
    } else {
        echo "<p style='color: orange;'>⚠ $column column does not exist</p>";
    }
}

$conn->close();

echo "<p><a href='admin_dashboard.php'>Back to Admin Dashboard</a></p>";
?>