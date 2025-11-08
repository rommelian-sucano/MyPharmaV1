<?php
include 'db.php';

echo "<h2>Direct Database Test</h2>";

// Direct query for pending pharmacies
$pending_result = $conn->query("SELECT * FROM pharmacies WHERE verified = 0 ORDER BY name");
if ($pending_result) {
    echo "<h3>Pending Pharmacies (verified = 0): " . $pending_result->num_rows . "</h3>";
    if ($pending_result->num_rows > 0) {
        echo "<ul>";
        while ($pharmacy = $pending_result->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($pharmacy['name']) . "</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p>Error: " . $conn->error . "</p>";
}

// Direct query for verified pharmacies
$verified_result = $conn->query("SELECT * FROM pharmacies WHERE verified = 1 ORDER BY name");
if ($verified_result) {
    echo "<h3>Verified Pharmacies (verified = 1): " . $verified_result->num_rows . "</h3>";
    if ($verified_result->num_rows > 0) {
        echo "<ul>";
        while ($pharmacy = $verified_result->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($pharmacy['name']) . "</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p>Error: " . $conn->error . "</p>";
}

$conn->close();
?>