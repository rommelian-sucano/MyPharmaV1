<?php
include 'db.php';

echo "<h2>Database Schema Check</h2>";

// Check if users table exists
$table_check = $conn->query("SHOW TABLES LIKE 'users'");
if ($table_check && $table_check->num_rows > 0) {
    echo "<p style='color: green;'>✓ Users table exists</p>";
    
    // Get table structure
    $structure = $conn->query("DESCRIBE users");
    if ($structure) {
        echo "<h3>Users Table Structure:</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = $structure->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: red;'>✗ Users table does not exist</p>";
}

// Check if pharmacies table exists
$table_check = $conn->query("SHOW TABLES LIKE 'pharmacies'");
if ($table_check && $table_check->num_rows > 0) {
    echo "<p style='color: green;'>✓ Pharmacies table exists</p>";
} else {
    echo "<p style='color: red;'>✗ Pharmacies table does not exist</p>";
}

// Check if notifications table exists
$table_check = $conn->query("SHOW TABLES LIKE 'notifications'");
if ($table_check && $table_check->num_rows > 0) {
    echo "<p style='color: green;'>✓ Notifications table exists</p>";
} else {
    echo "<p style='color: red;'>✗ Notifications table does not exist</p>";
}

$conn->close();
?>