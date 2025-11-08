<?php
include 'db.php';

echo "<h2>Database Connection Test</h2>";

// Test connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "<p style='color: green;'>✓ Database connection successful</p>";

// Check if tables exist
$tables = ['users', 'pharmacies', 'notifications'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        
        // Check if created_at column exists
        $columns = $conn->query("SHOW COLUMNS FROM $table LIKE 'created_at'");
        if ($columns && $columns->num_rows > 0) {
            echo "<p style='color: green;'>✓ Column 'created_at' exists in '$table' table</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Column 'created_at' does not exist in '$table' table</p>";
            echo "<p>Please run <a href='update_database.php'>update_database.php</a> to add missing columns</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Table '$table' does not exist</p>";
        echo "<p>Please import the init.sql file into your database</p>";
    }
}

$conn->close();
?>