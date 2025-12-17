<?php
include 'db.php';

echo "<h2>Map Data Test</h2>";

if ($conn->connect_error) {
    echo "<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color: green;'>Connected successfully to database server</p>";
    
    // Test if pharmacies table exists and has data
    $result = $conn->query("SELECT COUNT(*) as count FROM pharmacies");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p style='color: green;'>Pharmacies table exists with " . $row['count'] . " records</p>";
        
        // Show first 5 pharmacies
        $result = $conn->query("SELECT id, name, address, lat, lng FROM pharmacies LIMIT 5");
        if ($result && $result->num_rows > 0) {
            echo "<h3>Sample Pharmacies:</h3><ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['name']) . " (" . $row['lat'] . ", " . $row['lng'] . ")</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color: orange;'>Pharmacies table does not exist yet</p>";
    }
}

echo "<p><a href='index.php'>Back to MyPharma</a></p>";
?>