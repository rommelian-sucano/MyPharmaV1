<?php
include 'db.php';

echo "<h1>MyPharma Database Detailed Test</h1>";

// Test pharmacies table structure
echo "<h2>Pharmacies Table Structure:</h2>";
$result = $conn->query("DESCRIBE pharmacies");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while($row = $result->fetch_assoc()) {
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

// Test the specific query that was causing the error
echo "<h2>Testing Pharmacy Query:</h2>";
$stmt = $conn->prepare("SELECT id, name, address FROM pharmacies WHERE user_id = ? LIMIT 1");
if ($stmt) {
    echo "<p style='color: green;'>✓ Query with 'name' column works correctly!</p>";
    $stmt->close();
} else {
    echo "<p style='color: red;'>✗ Query failed: " . $conn->error . "</p>";
}

// Test if pharmacy_name column exists (it shouldn't)
echo "<h2>Checking for 'pharmacy_name' column:</h2>";
$result = $conn->query("SHOW COLUMNS FROM pharmacies LIKE 'pharmacy_name'");
if ($result->num_rows > 0) {
    echo "<p style='color: red;'>✗ Column 'pharmacy_name' exists (this shouldn't be the case)</p>";
} else {
    echo "<p style='color: green;'>✓ Column 'pharmacy_name' does not exist (as expected)</p>";
}

// Test a sample query to get pharmacy data
echo "<h2>Sample Pharmacy Data:</h2>";
$result = $conn->query("SELECT id, name, address FROM pharmacies LIMIT 3");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Address</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['address']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p style='color: green;'>✓ Successfully retrieved pharmacy data</p>";
} else {
    echo "<p>No pharmacy data found or query failed</p>";
}

echo "<h2>Test Complete</h2>";
echo "<p>If all tests show green checkmarks, your database is properly configured and the fix should work.</p>";
?>