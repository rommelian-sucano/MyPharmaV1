<?php
// Authentication and security helper functions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate CSRF token
 */
function getCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken(?string $token): bool {
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require user to be logged in
 */
function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Require specific user role
 */
function requireRole(array $allowedRoles): void {
    requireLogin();
    $role = $_SESSION['role'] ?? '';
    if (!in_array($role, $allowedRoles, true)) {
        http_response_code(403);
        echo "Access denied. Insufficient permissions.";
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
 * Escape output for security
 */
function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to specific page
 */
function redirect(string $url): void {
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
 * Check if user is staff
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
 * Add notification to database
 */
function addNotification($conn, string $message, string $type = 'info'): bool {
    try {
        $stmt = $conn->prepare("INSERT INTO notifications (message, type, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $message, $type);
        return $stmt->execute();
    } catch (Exception $e) {
        // If notifications table doesn't exist, fail silently
        error_log("Notification error: " . $e->getMessage());
        return false;
    }
}
?>