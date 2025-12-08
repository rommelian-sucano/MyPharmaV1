<?php
// ============================================
// AUTHENTICATION AND SECURITY HELPER FUNCTIONS
// WITH AUTO-LOGOUT FUNCTIONALITY
// ============================================

// ============================================
// SESSION SECURITY CONFIGURATION
// ============================================
function initSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session cookie parameters
        ini_set('session.cookie_lifetime', 0);      // Expire on browser close
        ini_set('session.cookie_httponly', 1);      // Prevent JavaScript access
        ini_set('session.cookie_secure', false);    // Set to true if using HTTPS
        ini_set('session.use_only_cookies', 1);     // Prevent session fixation via URL
        ini_set('session.use_strict_mode', 1);      // Reject uninitialized session IDs
        ini_set('session.cookie_samesite', 'Lax');  // CSRF protection
        
        session_start();
        
        // Regenerate session ID periodically to prevent fixation
        $regenerateInterval = 300; // 5 minutes
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
            session_regenerate_id(true);
        } elseif (time() - $_SESSION['last_regeneration'] > $regenerateInterval) {
            $_SESSION['last_regeneration'] = time();
            session_regenerate_id(true);
        }
    }
}

// Initialize secure session
initSecureSession();

// ============================================
// SESSION TIMEOUT MANAGEMENT
// ============================================
function checkSessionTimeout(): void {
    $timeout = 1800; // 30 minutes in seconds
    
    // Initialize last activity if not set
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        $_SESSION['session_start'] = time();
        return;
    }
    
    $session_life = time() - $_SESSION['last_activity'];
    
    // Check if session has expired
    if ($session_life > $timeout) {
        // Log the timeout event
        error_log("Session timeout for user ID: " . ($_SESSION['user_id'] ?? 'unknown') . 
                  " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        
        // Clear session data
        $_SESSION = array();
        
        // Destroy session
        if (session_id() != "") {
            session_destroy();
        }
        
        // Clear session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Redirect to login with timeout message
        header("Location: login.php?error=session_expired&timeout=" . $timeout);
        exit();
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
}

// ============================================
// SECURITY FUNCTIONS
// ============================================

/**
 * Generate CSRF token
 */
function getCsrfToken(): string {
    if (empty($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_expiry'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_expiry'] = time() + 3600; // 1 hour expiry
    } elseif (time() > $_SESSION['csrf_token_expiry']) {
        // Regenerate expired token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_expiry'] = time() + 3600;
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token with expiry check
 */
function verifyCsrfToken(?string $token): bool {
    if (!isset($_SESSION['csrf_token'], $_SESSION['csrf_token_expiry'])) {
        return false;
    }
    
    // Check if token expired
    if (time() > $_SESSION['csrf_token_expiry']) {
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_expiry']);
        return false;
    }
    
    return is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require user to be logged in with session timeout check
 */
function requireLogin(): void {
    // First check session timeout
    checkSessionTimeout();
    
    // Then check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        // Log the access attempt
        error_log("Unauthorized access attempt - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        
        header("Location: login.php?error=unauthorized");
        exit();
    }
    
    // Log successful access (optional for auditing)
    if (!isset($_SESSION['last_access_log']) || time() - $_SESSION['last_access_log'] > 300) {
        error_log("User " . ($_SESSION['user_id'] ?? 'unknown') . " accessed protected page - " . 
                  $_SERVER['REQUEST_URI'] ?? 'unknown');
        $_SESSION['last_access_log'] = time();
    }
}

/**
 * Require specific user role with enhanced security
 */
function requireRole(array $allowedRoles): void {
    requireLogin();
    
    $role = $_SESSION['role'] ?? '';
    $userId = $_SESSION['user_id'] ?? '';
    
    if (!in_array($role, $allowedRoles, true)) {
        // Log unauthorized role attempt
        error_log("Unauthorized role attempt - User: $userId, Role: $role, " . 
                  "Required: " . implode(',', $allowedRoles) . ", IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        
        http_response_code(403);
        include_once 'unauthorized.php'; // Make sure this file exists
        exit();
    }
}

/**
 * Check if user has specific role
 */
function hasRole(string $role): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Escape output for security with null safety
 */
function e(?string $value): string {
    if ($value === null) {
        return '';
    }
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
}

/**
 * Redirect to specific page with session preservation
 */
function redirect(string $url): void {
    // Save any flash messages before redirect
    if (isset($_SESSION['flash_messages'])) {
        // Flash messages persist for one request
    }
    
    header("Location: $url");
    exit();
}

/**
 * Check if user is admin
 */
function isAdmin(): bool {
    return hasRole('admin');
}

/**
 * Check if user is staff (includes admin)
 */
function isStaff(): bool {
    return hasRole('staff') || hasRole('owner') || hasRole('admin');
}

/**
 * Get user ID from session
 */
function getUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get user's role from session
 */
function getUserRole(): ?string {
    return $_SESSION['role'] ?? null;
}

/**
 * Get session remaining time in seconds
 */
function getSessionRemainingTime(): int {
    if (!isset($_SESSION['last_activity'])) {
        return 1800; // Default 30 minutes
    }
    
    $elapsed = time() - $_SESSION['last_activity'];
    $remaining = 1800 - $elapsed;
    
    return max(0, $remaining); // Never return negative
}

/**
 * Get session remaining time formatted (MM:SS)
 */
function getSessionRemainingTimeFormatted(): string {
    $seconds = getSessionRemainingTime();
    $minutes = floor($seconds / 60);
    $remainingSeconds = $seconds % 60;
    
    return sprintf('%02d:%02d', $minutes, $remainingSeconds);
}

/**
 * Check if session is about to expire (less than 5 minutes)
 */
function isSessionAboutToExpire(): bool {
    return getSessionRemainingTime() < 300; // 5 minutes
}

/**
 * Extend session (reset activity timer)
 */
function extendSession(): void {
    if (isset($_SESSION['user_id'])) {
        $_SESSION['last_activity'] = time();
        
        // Log session extension for auditing
        error_log("Session extended for user " . $_SESSION['user_id'] . 
                  " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    }
}

/**
 * Destroy session completely (secure logout)
 */
function destroySession(): void {
    $userId = $_SESSION['user_id'] ?? 'unknown';
    
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session
    if (session_id() != "") {
        session_destroy();
    }
    
    // Clear session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
        unset($_COOKIE[session_name()]);
    }
    
    // Log logout event
    error_log("User $userId logged out - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}

/**
 * Add notification to database with security context
 */
function addNotification($conn, string $message, string $type = 'info', ?int $userId = null): bool {
    try {
        $userId = $userId ?? $_SESSION['user_id'] ?? null;
        $userIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        if ($conn === null) {
            error_log("Database connection is null in addNotification");
            return false;
        }
        
        // Check if notifications table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
        if ($tableCheck && $tableCheck->num_rows > 0) {
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, ip_address, created_at) 
                                   VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("isss", $userId, $message, $type, $userIp);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Notification error: " . $e->getMessage() . 
                  " - User: " . ($userId ?? 'unknown') . 
                  " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        return false;
    }
}

/**
 * Add flash message for one-time display
 */
function addFlashMessage(string $type, string $message): void {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message,
        'timestamp' => time()
    ];
}

/**
 * Get and clear flash messages
 */
function getFlashMessages(): array {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Validate user input against XSS attacks
 */
function sanitizeInput(string $input): string {
    // Remove null bytes
    $input = str_replace(chr(0), '', $input);
    
    // Remove control characters except newlines and tabs
    $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
    
    // Convert special characters to HTML entities
    $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    
    return trim($input);
}

/**
 * Get client IP address (with proxy support)
 */
function getClientIp(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    // Check for proxy headers (use with caution)
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $forwarded = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($forwarded[0]);
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    
    // Validate IP format
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $ip = '0.0.0.0';
    }
    
    return $ip;
}

/**
 * Log security event
 */
function logSecurityEvent(string $event, string $details = ''): void {
    $userId = $_SESSION['user_id'] ?? 'guest';
    $ip = getClientIp();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $timestamp = date('Y-m-d H:i:s');
    
    $logMessage = "[$timestamp] [$ip] [User: $userId] [$event] $details [UA: $userAgent]";
    
    // Log to file
    $logFile = __DIR__ . '/security.log';
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
    
    // Also log to PHP error log
    error_log($logMessage);
}

/**
 * Generate secure password hash
 */
function generatePasswordHash(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
}

/**
 * Verify password against hash
 */
function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

/**
 * Generate secure random token
 */
function generateSecureToken(int $length = 32): string {
    return bin2hex(random_bytes($length));
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest(): bool {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Send JSON response (for AJAX calls)
 */
function sendJsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

// ============================================
// SESSION AUTO-LOGOUT CONFIGURATION
// ============================================

// Check session timeout on every request
checkSessionTimeout();

// Add automatic logout JavaScript for browser close detection
function addAutoLogoutScript(): string {
    return '
    <script>
    // Auto-logout functionality
    let lastActivity = Date.now();
    const SESSION_TIMEOUT = ' . getSessionRemainingTime() . ' * 1000;
    
    function updateActivity() {
        lastActivity = Date.now();
        // Send heartbeat to keep session alive
        if (navigator.sendBeacon) {
            navigator.sendBeacon("keepalive.php");
        }
    }
    
    // Track user activity
    ["mousemove", "keydown", "click", "scroll", "touchstart"].forEach(event => {
        document.addEventListener(event, updateActivity);
    });
    
    // Browser close detection
    window.addEventListener("beforeunload", function() {
        if (navigator.sendBeacon) {
            navigator.sendBeacon("logout_on_close.php");
        }
    });
    </script>
    ';
}

// ============================================
// DEBUG HELPER FUNCTIONS
// ============================================

/**
 * Dump variable for debugging (safe for production)
 */
function debugDump($var, bool $return = false): ?string {
    if (!defined('DEBUG_MODE') || !DEBUG_MODE) {
        return null;
    }
    
    ob_start();
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    $output = ob_get_clean();
    
    if ($return) {
        return $output;
    }
    
    echo $output;
    return null;
}

/**
 * Check if running in development mode
 */
function isDevelopmentMode(): bool {
    return $_SERVER['SERVER_NAME'] === 'localhost' || 
           $_SERVER['SERVER_ADDR'] === '127.0.0.1' ||
           (defined('ENVIRONMENT') && ENVIRONMENT === 'development');
}

// ============================================
// INITIALIZATION
// ============================================

// Log session initialization for debugging
if (isDevelopmentMode() && isset($_SESSION['user_id'])) {
    error_log("Session initialized for user " . $_SESSION['user_id'] . 
              " - Role: " . ($_SESSION['role'] ?? 'unknown') . 
              " - IP: " . getClientIp());
}

// Initialize flash messages storage
if (!isset($_SESSION['flash_messages'])) {
    $_SESSION['flash_messages'] = [];
}

// Set default timezone if not set
if (!date_default_timezone_get()) {
    date_default_timezone_set('UTC');
}
?>
