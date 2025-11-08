<?php
include 'db.php';

echo "<h2>Testing Pharmacy Queries</h2>";

// Test pending pharmacies query
echo "<h3>Pending Pharmacies (verified = 0)</h3>";
$stmt = $conn->prepare("SELECT * FROM pharmacies WHERE verified = 0 ORDER BY name");
$stmt->execute();
$pending_result = $stmt->get_result();

if ($pending_result->num_rows > 0) {
    echo "<p>Found " . $pending_result->num_rows . " pending pharmacies:</p>";
    echo "<ul>";
    while ($pharmacy = $pending_result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($pharmacy['name']) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No pending pharmacies found.</p>";
}
$stmt->close();

// Test verified pharmacies query
echo "<h3>Verified Pharmacies (verified = 1)</h3>";
$stmt = $conn->prepare("SELECT * FROM pharmacies WHERE verified = 1 ORDER BY name");
$stmt->execute();
$verified_result = $stmt->get_result();

if ($verified_result->num_rows > 0) {
    echo "<p>Found " . $verified_result->num_rows . " verified pharmacies:</p>";
    echo "<ul>";
    while ($pharmacy = $verified_result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($pharmacy['name']) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No verified pharmacies found.</p>";
}
$stmt->close();

$conn->close();

echo "<h3>Debug Information</h3>";
echo "<p>This test confirms that:</p>";
echo "<ul>";
echo "<li>The database connection is working</li>";
echo "<li>The pharmacies table exists</li>";
echo "<li>The verified column exists</li>";
echo "<li>Queries can be executed successfully</li>";
echo "</ul>";
echo "<p>If this test shows 15 verified pharmacies but they don't appear in the admin dashboard, the issue is likely in the HTML display code.</p>";
?>