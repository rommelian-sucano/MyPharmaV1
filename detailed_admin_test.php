<?php
include 'db.php';

// Simulate the exact queries from admin_dashboard.php
// Get pending pharmacies
$stmt = $conn->prepare("SELECT * FROM pharmacies WHERE verified = 0 ORDER BY name");
$stmt->execute();
$pending_pharmacies_result = $stmt->get_result();
$stmt->close();

// Get verified pharmacies
$stmt = $conn->prepare("SELECT * FROM pharmacies WHERE verified = 1 ORDER BY name");
$stmt->execute();
$verified_pharmacies_result = $stmt->get_result();
$stmt->close();

echo "<h2>Detailed Admin Dashboard Test</h2>";

// This mimics the stats section
echo "<h3>Stats Section Test</h3>";
echo "<p>Pending Pharmacies Count: " . $pending_pharmacies_result->num_rows . "</p>";
echo "<p>Verified Pharmacies Count: " . $verified_pharmacies_result->num_rows . "</p>";

// This mimics the pending pharmacies display
echo "<h3>Pending Pharmacies Display Test</h3>";
if ($pending_pharmacies_result->num_rows === 0) {
    echo "<p>No pending pharmacy registrations.</p>";
} else {
    echo "<ul>";
    while ($pharmacy = $pending_pharmacies_result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($pharmacy['name']) . "</li>";
    }
    echo "</ul>";
}

// This mimics the verified pharmacies display
echo "<h3>Verified Pharmacies Display Test</h3>";
if ($verified_pharmacies_result->num_rows === 0) {
    echo "<p>No verified pharmacies.</p>";
} else {
    echo "<ul>";
    while ($pharmacy = $verified_pharmacies_result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($pharmacy['name']) . "</li>";
    }
    echo "</ul>";
}

$conn->close();
?>