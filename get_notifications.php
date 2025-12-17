<?php
header('Content-Type: application/json');
include 'db.php';

$notifications = [];

try {
    // Get recent notifications (last 5)
    $query = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $notifications[] = [
                'id' => $row['id'],
                'message' => $row['message'],
                'created_at' => $row['created_at']
            ];
        }
    }
} catch (Exception $e) {
    // If notifications table doesn't exist yet, return empty array
    $notifications = [];
}

echo json_encode($notifications);
$conn->close();
?>