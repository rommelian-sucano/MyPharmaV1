<?php
include 'db.php';

echo "<h2>Debug: All Pharmacies in Database</h2>";

// First check if created_at column exists
$columns = $conn->query("SHOW COLUMNS FROM pharmacies LIKE 'created_at'");
if ($columns && $columns->num_rows > 0) {
    // Get all pharmacies with created_at
    $stmt = $conn->prepare("SELECT id, name, address, contact, verified, created_at FROM pharmacies ORDER BY id DESC");
} else {
    // Get all pharmacies without created_at
    $stmt = $conn->prepare("SELECT id, name, address, contact, verified FROM pharmacies ORDER BY id DESC");
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Name</th><th>Address</th><th>Contact</th><th>Verified</th>";
    if ($columns && $columns->num_rows > 0) {
        echo "<th>Created At</th>";
    }
    echo "</tr>";
    
    while ($pharmacy = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $pharmacy['id'] . "</td>";
        echo "<td>" . htmlspecialchars($pharmacy['name']) . "</td>";
        echo "<td>" . htmlspecialchars($pharmacy['address']) . "</td>";
        echo "<td>" . htmlspecialchars($pharmacy['contact']) . "</td>";
        echo "<td>" . ($pharmacy['verified'] ? 'Yes' : 'No') . "</td>";
        if (isset($pharmacy['created_at'])) {
            echo "<td>" . $pharmacy['created_at'] . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No pharmacies found in the database.</p>";
}

$stmt->close();
$conn->close();

echo "<h3>Explanation</h3>";
echo "<p>In the MyPharma system:</p>";
echo "<ul>";
echo "<li><strong>Pending Pharmacies</strong>: Pharmacies with verified=0 (not yet approved by admin)</li>";
echo "<li><strong>Verified Pharmacies</strong>: Pharmacies with verified=1 (approved by admin)</li>";
echo "</ul>";
echo "<p>If you don't see pharmacies in the admin dashboard, check if they exist in the database and their verified status.</p>";
echo "<p>If the 'created_at' column is missing, run the update script at <a href='update_database.php'>update_database.php</a></p>";
?>