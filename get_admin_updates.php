<?php
header('Content-Type: application/json');
include 'db.php';

try {
    // Get pending user registrations
    // First check if created_at column exists
    $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");
    if ($columns && $columns->num_rows > 0) {
        $pending_users_stmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE status = 'pending' ORDER BY created_at DESC LIMIT 5");
    } else {
        $pending_users_stmt = $conn->prepare("SELECT id, name, email FROM users WHERE status = 'pending' ORDER BY id DESC LIMIT 5");
    }
    $pending_users_stmt->execute();
    $pending_users_result = $pending_users_stmt->get_result();
    
    $pending_users = [];
    while ($user = $pending_users_result->fetch_assoc()) {
        $user_data = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ];
        
        // Add created_at if it exists
        if (isset($user['created_at'])) {
            $user_data['created_at'] = $user['created_at'];
        }
        
        $pending_users[] = $user_data;
    }
    $pending_users_stmt->close();
    
    // Get pending pharmacies
    // First check if created_at column exists
    $columns = $conn->query("SHOW COLUMNS FROM pharmacies LIKE 'created_at'");
    if ($columns && $columns->num_rows > 0) {
        $pending_pharmacies_stmt = $conn->prepare("SELECT id, name, address, contact, created_at FROM pharmacies WHERE verified = 0 ORDER BY created_at DESC LIMIT 5");
    } else {
        $pending_pharmacies_stmt = $conn->prepare("SELECT id, name, address, contact FROM pharmacies WHERE verified = 0 ORDER BY id DESC LIMIT 5");
    }
    $pending_pharmacies_stmt->execute();
    $pending_pharmacies_result = $pending_pharmacies_stmt->get_result();
    
    $pending_pharmacies = [];
    while ($pharmacy = $pending_pharmacies_result->fetch_assoc()) {
        $pharmacy_data = [
            'id' => (int)$pharmacy['id'],
            'name' => $pharmacy['name'],
            'address' => $pharmacy['address'],
            'contact' => $pharmacy['contact']
        ];
        
        // Add created_at if it exists
        if (isset($pharmacy['created_at'])) {
            $pharmacy_data['created_at'] = $pharmacy['created_at'];
        }
        
        $pending_pharmacies[] = $pharmacy_data;
    }
    $pending_pharmacies_stmt->close();
    
    // Get recent notifications
    // First check if created_at column exists
    $columns = $conn->query("SHOW COLUMNS FROM notifications LIKE 'created_at'");
    if ($columns && $columns->num_rows > 0) {
        $notifications_stmt = $conn->prepare("SELECT id, type, message, created_at FROM notifications ORDER BY created_at DESC LIMIT 5");
    } else {
        $notifications_stmt = $conn->prepare("SELECT id, type, message FROM notifications ORDER BY id DESC LIMIT 5");
    }
    $notifications_stmt->execute();
    $notifications_result = $notifications_stmt->get_result();
    
    $notifications = [];
    while ($notification = $notifications_result->fetch_assoc()) {
        $notification_data = [
            'id' => (int)$notification['id'],
            'type' => $notification['type'],
            'message' => $notification['message']
        ];
        
        // Add created_at if it exists
        if (isset($notification['created_at'])) {
            $notification_data['created_at'] = $notification['created_at'];
        }
        
        $notifications[] = $notification_data;
    }
    $notifications_stmt->close();
    
    // Get stats
    $stats = [];
    
    // Total users
    $users_stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE status = 'approved'");
    $users_stmt->execute();
    $users_result = $users_stmt->get_result();
    $stats['total_users'] = (int)$users_result->fetch_assoc()['count'];
    $users_stmt->close();
    
    // Total pharmacies
    $pharmacies_stmt = $conn->prepare("SELECT COUNT(*) as count FROM pharmacies WHERE verified = 1");
    $pharmacies_stmt->execute();
    $pharmacies_result = $pharmacies_stmt->get_result();
    $stats['total_pharmacies'] = (int)$pharmacies_result->fetch_assoc()['count'];
    $pharmacies_stmt->close();
    
    // Pending users
    $stats['pending_users'] = count($pending_users);
    
    // Pending pharmacies
    $stats['pending_pharmacies'] = count($pending_pharmacies);
    
    echo json_encode([
        'success' => true,
        'pending_users' => $pending_users,
        'pending_pharmacies' => $pending_pharmacies,
        'notifications' => $notifications,
        'stats' => $stats
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>