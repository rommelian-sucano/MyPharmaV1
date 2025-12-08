<?php
// logout_on_close.php - Handle logout on browser close
require_once 'auth.php';

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    destroySession();
    
    // Log browser close logout
    logSecurityEvent('BROWSER_CLOSE_LOGOUT', "User $userId logged out via browser close");
    
    if (isAjaxRequest()) {
        sendJsonResponse(['status' => 'success', 'message' => 'Logged out']);
    }
}

exit();
?>
