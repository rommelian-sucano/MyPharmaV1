<?php
include 'db.php';

echo "<h1>Testing Staff Dashboard Fix</h1>";

// Test 1: Check if we can query pharmacy info from users table
echo "<h2>Test 1: Querying pharmacy info from users table</h2>";
$stmt = $conn->prepare("SELECT id, pharmacy_name, pharmacy_address FROM users LIMIT 1");
if ($stmt) {
    echo "<p style='color: green;'>✓ Query works correctly!</p>";
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo "<p>Sample data: " . htmlspecialchars($row['pharmacy_name'] ?? 'N/A') . "</p>";
    }
    $stmt->close();
} else {
    echo "<p style='color: red;'>✗ Query failed: " . $conn->error . "</p>";
}

// Test 2: Check if we can query pharmacies by name
echo "<h2>Test 2: Querying pharmacies by name</h2>";
$stmt = $conn->prepare("SELECT id, name, address FROM pharmacies LIMIT 1");
if ($stmt) {
    echo "<p style='color: green;'>✓ Query works correctly!</p>";
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo "<p>Sample data: " . htmlspecialchars($row['name']) . "</p>";
    }
    $stmt->close();
} else {
    echo "<p style='color: red;'>✗ Query failed: " . $conn->error . "</p>";
}

echo "<h2>Test Complete</h2>";
?>