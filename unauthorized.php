<?php
// ============================================
// UNAUTHORIZED ACCESS PAGE
// With auto-logout security features
// ============================================

// Start session to check if user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If user is logged in, log this unauthorized attempt
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'] ?? 'unknown';
    $userRole = $_SESSION['role'] ?? 'unknown';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $requestedPage = $_SERVER['REQUEST_URI'] ?? 'unknown';
    
    // Log the unauthorized access attempt
    error_log("Unauthorized access attempt - User: $userId, Role: $userRole, " .
              "Page: $requestedPage, IP: $ip");
    
    // You could also destroy the session here for security
    // session_destroy();
}

// Clear any sensitive session data but keep user info for redirect
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? 'guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .access-denied-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .access-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .security-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            border-left: 4px solid #dc3545;
        }
        
        .btn-custom {
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 10px;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-login {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
        }
        
        .btn-home {
            background: white;
            color: #333;
            border: 2px solid #667eea;
        }
        
        .session-info {
            font-size: 0.9rem;
            color: #666;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .access-details {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.9rem;
            text-align: left;
        }
        
        .access-details ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        
        .access-details li {
            margin-bottom: 5px;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .access-denied-card {
                padding: 30px 20px;
                margin: 10px;
            }
            
            .access-icon {
                font-size: 60px;
            }
            
            .btn-custom {
                display: block;
                width: 100%;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="access-denied-card">
        <!-- Animated Icon -->
        <div class="access-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        
        <!-- Main Message -->
        <h1 class="text-danger mb-3">
            <i class="fas fa-ban me-2"></i>Access Denied
        </h1>
        
        <p class="lead mb-4">
            You don't have permission to access this page.
        </p>
        
        <?php if ($isLoggedIn): ?>
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Logged in as:</strong> <?php echo htmlspecialchars($userRole); ?> role
            </div>
        <?php else: ?>
            <div class="alert alert-warning mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Not logged in:</strong> You need to login first
            </div>
        <?php endif; ?>
        
        <!-- Security Information -->
        <div class="security-info">
            <h5 class="mb-3">
                <i class="fas fa-lock me-2"></i>Security Information
            </h5>
            <p class="mb-0">
                <i class="fas fa-check-circle text-success me-2"></i>
                Auto-logout protection is active (30 minutes of inactivity)
            </p>
            <small class="text-muted">
                For security, your session will automatically end when you close the browser
            </small>
        </div>
        
        <!-- Access Details (for debugging/development) -->
        <?php if (isset($_SERVER['SERVER_ADDR']) && ($_SERVER['SERVER_ADDR'] === '127.0.0.1' || $_SERVER['SERVER_NAME'] === 'localhost')): ?>
            <div class="access-details mt-4">
                <h6><i class="fas fa-bug me-2"></i>Debug Information</h6>
                <ul>
                    <li><strong>User Role:</strong> <?php echo htmlspecialchars($userRole); ?></li>
                    <li><strong>Logged In:</strong> <?php echo $isLoggedIn ? 'Yes' : 'No'; ?></li>
                    <li><strong>Requested Page:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'Unknown'); ?></li>
                    <li><strong>IP Address:</strong> <?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'Unknown'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div class="mt-5">
            <?php if ($isLoggedIn): ?>
                <a href="logout.php" class="btn btn-danger btn-custom">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout & Return to Login
                </a>
                <a href="index.php" class="btn btn-home btn-custom">
                    <i class="fas fa-home me-2"></i>Go to Home Page
                </a>
                <?php if ($userRole === 'staff'): ?>
                    <a href="staff_dashboard.php" class="btn btn-primary btn-custom">
                        <i class="fas fa-tachometer-alt me-2"></i>Staff Dashboard
                    </a>
                <?php elseif ($userRole === 'admin'): ?>
                    <a href="admin_dashboard.php" class="btn btn-primary btn-custom">
                        <i class="fas fa-user-shield me-2"></i>Admin Dashboard
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php" class="btn btn-login btn-custom">
                    <i class="fas fa-sign-in-alt me-2"></i>Login to Continue
                </a>
                <a href="index.php" class="btn btn-home btn-custom">
                    <i class="fas fa-home me-2"></i>Go to Home Page
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Session Information -->
        <div class="session-info">
            <div class="row">
                <div class="col-md-6">
                    <small>
                        <i class="fas fa-clock text-info me-1"></i>
                        Auto-logout: <span class="text-success">Active</span>
                    </small>
                </div>
                <div class="col-md-6">
                    <small>
                        <i class="fas fa-shield-alt text-warning me-1"></i>
                        Session Security: <span class="text-success">Enabled</span>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-Logout Security Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-logout functionality for this page
        let lastActivity = Date.now();
        const INACTIVITY_TIMEOUT = 30 * 60 * 1000; // 30 minutes
        
        function updateActivity() {
            lastActivity = Date.now();
        }
        
        function checkInactivity() {
            const currentTime = Date.now();
            const inactiveTime = currentTime - lastActivity;
            
            // If inactive for more than 30 minutes, redirect to login
            if (inactiveTime > INACTIVITY_TIMEOUT) {
                window.location.href = 'login.php?error=session_expired';
            }
            
            // Update countdown display (optional)
            const remainingMinutes = Math.floor((INACTIVITY_TIMEOUT - inactiveTime) / 60000);
            const remainingSeconds = Math.floor(((INACTIVITY_TIMEOUT - inactiveTime) % 60000) / 1000);
            
            // Show warning when less than 5 minutes remain
            if (remainingMinutes < 5) {
                document.querySelector('.session-info').innerHTML = `
                    <div class="alert alert-warning py-2 mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Session expires in ${remainingMinutes}:${remainingSeconds.toString().padStart(2, '0')}
                    </div>
                `;
            }
        }
        
        // Track user activity
        ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(eventType => {
            document.addEventListener(eventType, updateActivity);
        });
        
        // Check inactivity every minute
        setInterval(checkInactivity, 60000);
        
        // Initial check
        checkInactivity();
        
        // Browser close detection
        window.addEventListener('beforeunload', function() {
            // If user is logged in, try to send logout signal
            if (<?php echo $isLoggedIn ? 'true' : 'false'; ?>) {
                // Use sendBeacon if available
                if (navigator.sendBeacon) {
                    navigator.sendBeacon('logout_on_close.php');
                }
            }
        });
        
        // Add some visual feedback for buttons
        document.querySelectorAll('.btn-custom').forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
        
        // Show security notification after 20 minutes of inactivity
        setTimeout(() => {
            if (<?php echo $isLoggedIn ? 'true' : 'false'; ?>) {
                const notification = document.createElement('div');
                notification.className = 'alert alert-info alert-dismissible fade show position-fixed';
                notification.style.bottom = '20px';
                notification.style.right = '20px';
                notification.style.zIndex = '9999';
                notification.style.maxWidth = '300px';
                notification.innerHTML = `
                    <i class="fas fa-clock me-2"></i>
                    <strong>Session Reminder:</strong> 10 minutes until auto-logout
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(notification);
                
                // Auto-dismiss after 10 seconds
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 10000);
            }
        }, 20 * 60 * 1000); // 20 minutes
    </script>
</body>
</html>
