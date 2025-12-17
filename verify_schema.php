<?php
include 'db.php';

echo "<h2>Database Schema Verification</h2>";

// Check users table structure
echo "<h3>Users Table Structure</h3>";
$users_columns = $conn->query("SHOW COLUMNS FROM users");
if ($users_columns) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $users_columns->fetch_assoc()) {
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
} else {
    echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
}

// Check pharmacies table structure
echo "<h3>Pharmacies Table Structure</h3>";
$pharmacies_columns = $conn->query("SHOW COLUMNS FROM pharmacies");
if ($pharmacies_columns) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $pharmacies_columns->fetch_assoc()) {
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
} else {
    echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
}

// Check if we can insert a test user with pharmacy information
echo "<h3>Testing User Registration with Pharmacy Information</h3>";
$stmt = $conn->prepare("INSERT INTO users (name, email, password, role, pharmacy_name, pharmacy_address, pharmacy_lat, pharmacy_lng, pharmacy_contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
if ($stmt) {
    $name = "Test Pharmacy Owner";
    $email = "test" . time() . "@example.com";
    $password = password_hash("password123", PASSWORD_DEFAULT);
    $role = "pending";
    $pharmacy_name = "Test Pharmacy";
    $pharmacy_address = "123 Test Street, Test City";
    $pharmacy_lat = "7.82300000";
    $pharmacy_lng = "123.43000000";
    $pharmacy_contact = "09123456789";
    
    $stmt->bind_param("ssssssdds", $name, $email, $password, $role, $pharmacy_name, $pharmacy_address, $pharmacy_lat, $pharmacy_lng, $pharmacy_contact);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        echo "<p style='color: green;'>✓ Successfully inserted test user with pharmacy information (ID: $user_id)</p>";
        
        // Retrieve and display the inserted user
        $selectStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $selectStmt->bind_param("i", $user_id);
        $selectStmt->execute();
        $result = $selectStmt->get_result();
        if ($user = $result->fetch_assoc()) {
            echo "<p>Retrieved user data:</p>";
            echo "<ul>";
            echo "<li>Name: " . htmlspecialchars($user['name']) . "</li>";
            echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
            echo "<li>Role: " . $user['role'] . "</li>";
            echo "<li>Pharmacy Name: " . htmlspecialchars($user['pharmacy_name']) . "</li>";
            echo "<li>Pharmacy Address: " . htmlspecialchars($user['pharmacy_address']) . "</li>";
            echo "<li>Pharmacy Lat: " . $user['pharmacy_lat'] . "</li>";
            echo "<li>Pharmacy Lng: " . $user['pharmacy_lng'] . "</li>";
            echo "<li>Pharmacy Contact: " . htmlspecialchars($user['pharmacy_contact']) . "</li>";
            echo "</ul>";
        }
        $selectStmt->close();
        
        // Clean up test user
        $deleteStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $deleteStmt->bind_param("i", $user_id);
        $deleteStmt->execute();
        $deleteStmt->close();
        echo "<p style='color: blue;'>ℹ Cleaned up test user</p>";
    } else {
        echo "<p style='color: red;'>✗ Error inserting test user: " . $stmt->error . "</p>";
    }
    $stmt->close();
} else {
    echo "<p style='color: red;'>✗ Error preparing statement: " . $conn->error . "</p>";
}

$conn->close();

echo "<h3>Schema Verification Complete</h3>";
echo "<p>If all tests passed, your database schema is correctly updated with the new pharmacy information fields.</p>";
?>