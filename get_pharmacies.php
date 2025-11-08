<?php
header('Content-Type: application/json');
include 'db.php';

try {
    $query = "SELECT id, name, address, lat, lng, contact FROM pharmacies WHERE verified = 1";
    $result = $conn->query($query);
    
    $pharmacies = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $pharmacies[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'address' => $row['address'],
                'lat' => (float)$row['lat'],
                'lng' => (float)$row['lng'],
                'contact' => $row['contact']
            ];
        }
    }
    
    echo json_encode($pharmacies);
} catch (Exception $e) {
    // Return empty array if there's an error
    echo json_encode([]);
}

$conn->close();
?>