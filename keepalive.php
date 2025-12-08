<?php
// keepalive.php - Keeps session alive during activity

session_start();

if (isset($_SESSION['user_id'])) {
    // Update last activity time
    $_SESSION['last_activity'] = time();
    
    // Send minimal response
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'last_activity' => $_SESSION['last_activity'],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
}
exit();
?>
