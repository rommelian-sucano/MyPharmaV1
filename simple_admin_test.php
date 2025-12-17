<?php
//session_start();
include 'db.php';

// Check if user is logged in and is admin
//if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//    echo "You need to be logged in as admin to view this page.";
//    exit();
//}

// For debugging, let's simulate being logged in as admin
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

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

echo "<h2>Admin Dashboard Test</h2>";

echo "<h3>Pending Pharmacies Count: " . $pending_pharmacies_result->num_rows . "</h3>";
if ($pending_pharmacies_result->num_rows > 0) {
    echo "<ul>";
    while ($pharmacy = $pending_pharmacies_result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($pharmacy['name']) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No pending pharmacies</p>";
}

echo "<h3>Verified Pharmacies Count: " . $verified_pharmacies_result->num_rows . "</h3>";
if ($verified_pharmacies_result->num_rows > 0) {
    echo "<ul>";
    while ($pharmacy = $verified_pharmacies_result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($pharmacy['name']) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No verified pharmacies</p>";
}

$conn->close();
?>