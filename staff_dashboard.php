<?php
// ============================================
// STAFF DASHBOARD - FIXED VERSION
// ============================================

// ENABLE ERROR REPORTING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ============================================
// SESSION SETTINGS FOR AUTO LOGOUT
// ============================================
// Set session cookie to expire when browser closes
ini_set('session.cookie_lifetime', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

session_start();

// ============================================
// SESSION TIMEOUT CHECK (30 minutes)
// ============================================
$timeout = 1800; // 30 minutes in seconds

if (isset($_SESSION['last_activity'])) {
    $session_life = time() - $_SESSION['last_activity'];
    
    // If session expired, destroy it and redirect to login
    if ($session_life > $timeout) {
        // Destroy session
        session_unset();
        session_destroy();
        
        // Clear session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Redirect to login with timeout message
        header("Location: login.php?error=session_expired");
        exit();
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();

// DEBUG HEADER
echo "<!-- DEBUG MODE ACTIVE -->";
echo "<!-- PHP Version: " . phpversion() . " -->";
echo "<!-- Session ID: " . session_id() . " -->";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
if (!file_exists('db.php')) {
    die("ERROR: db.php file not found. Please check file exists in: " . __DIR__);
}

include 'db.php';

// Check database connection
if ($conn->connect_error) {
    die("ERROR: Database connection failed: " . $conn->connect_error);
}

// ============================================
// HELPER FUNCTIONS (MISSING IN YOUR ORIGINAL)
// ============================================

function e($data) {
    if (is_null($data)) return '';
    if (is_array($data)) return 'Array';
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}

function requireRole($allowed_roles = []) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: login.php');
        exit();
    }
    
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        // Create unauthorized.php if it doesn't exist
        header('Location: unauthorized.php');
        exit();
    }
    
    return true;
}

// ============================================
// MAIN DASHBOARD CODE
// ============================================

// Check role (you're admin, so this should pass)
requireRole(['staff', 'owner', 'admin']);

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$debug_mode = isset($_GET['debug']);

// Initialize variables with safe defaults
$pharmacy = null;
$system_status = 'checking';
$debug_info = [];

// SIMPLIFIED DATABASE QUERIES - Removed complex joins that might fail
try {
    // Check if users table has our user
    $debug_info[] = "Checking user ID: " . $user_id;
    
    // Simple query first
    $stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id = ?");
    if (!$stmt) {
        $debug_info[] = "User query prepare failed: " . $conn->error;
    } else {
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            $debug_info[] = "User query execute failed: " . $stmt->error;
        } else {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $user_data = $result->fetch_assoc();
                $debug_info[] = "User found: " . $user_data['name'];
            } else {
                $debug_info[] = "WARNING: User ID " . $user_id . " not found in users table";
            }
        }
        $stmt->close();
    }
    
    // Check for pharmacy info - SIMPLIFIED
    $debug_info[] = "Checking for pharmacy info...";
    
    // Try simple query first
    $stmt = $conn->prepare("SELECT pharmacy_name, pharmacy_address FROM users WHERE id = ? AND pharmacy_name IS NOT NULL");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $pharmacy = $result->fetch_assoc();
            $system_status = 'legacy';
            $debug_info[] = "Found pharmacy in users table: " . $pharmacy['pharmacy_name'];
        }
        $stmt->close();
    }
    
    // Initialize medicine counts
    $total_medicines = 0;
    $in_stock_count = 0;
    $low_stock_count = 0;
    $out_of_stock_count = 0;
    
} catch (Exception $e) {
    $debug_info[] = "Database error: " . $e->getMessage();
}

// Create unauthorized.php if it doesn't exist
$unauthorized_file = 'unauthorized.php';
if (!file_exists($unauthorized_file)) {
    file_put_contents($unauthorized_file, '<?php echo "<h1>Access Denied</h1><p>You don\'t have permission to view this page.</p>"; ?>');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Simple styles - removed complex CSS that might conflict */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            background: #2c3e50;
            color: white;
            min-height: 100vh;
        }
        .main-content {
            padding: 20px;
        }
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .debug-panel {
            font-size: 12px;
            background: #f1f1f1;
            border-left: 4px solid #dc3545;
        }
        .session-timer {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 10px 15px;
            border-radius: 20px;
            font-size: 14px;
            z-index: 1000;
            display: none;
        }
    </style>
</head>
<body>
    <!-- Session Timer (shows time until auto logout) -->
    <div id="sessionTimer" class="session-timer">
        <i class="fas fa-clock me-2"></i>
        Session expires in: <span id="countdown">30:00</span>
    </div>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Debug Panel -->
            <?php if ($debug_mode): ?>
            <div class="col-12">
                <div class="card debug-panel mt-3">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0">Debug Information</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($debug_info as $info): ?>
                            <div><?php echo e($info); ?></div>
                        <?php endforeach; ?>
                        <hr>
                        <div><strong>Session Data:</strong></div>
                        <div>User ID: <?php echo e($_SESSION['user_id'] ?? 'Not set'); ?></div>
                        <div>Name: <?php echo e($_SESSION['name'] ?? 'Not set'); ?></div>
                        <div>Role: <?php echo e($_SESSION['role'] ?? 'Not set'); ?></div>
                        <div>Email: <?php echo e($_SESSION['email'] ?? 'Not set'); ?></div>
                        <div>Last Activity: <?php echo e(isset($_SESSION['last_activity']) ? date('H:i:s', $_SESSION['last_activity']) : 'Not set'); ?></div>
                        <div>Session Lifetime: <?php echo e(isset($_SESSION['last_activity']) ? (time() - $_SESSION['last_activity']) : 'N/A'); ?> seconds</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 sidebar d-md-block">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-user-md fa-2x"></i>
                        </div>
                        <h4>Staff Panel</h4>
                        <p class="mb-0"><?php echo e($_SESSION['name'] ?? 'User'); ?></p>
                        <small class="text-light"><?php echo e(ucfirst($_SESSION['role'] ?? 'user')); ?></small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="staff_dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="manage_medicines.php">
                                <i class="fas fa-pills me-2"></i>Manage Medicines
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="inventory.php">
                                <i class="fas fa-boxes me-2"></i>Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="add_medicine.php">
                                <i class="fas fa-plus-circle me-2"></i>Add Medicine
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="index.php">
                                <i class="fas fa-home me-2"></i>Home
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-warning" href="logout.php" onclick="sendLogoutSignal()">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Session Status in Sidebar -->
                    <div class="mt-4 pt-3 border-top border-secondary">
                        <div class="text-center">
                            <small class="text-light">Session Status</small>
                            <div class="progress mt-2" style="height: 6px;">
                                <div id="sessionProgress" class="progress-bar bg-success" 
                                     style="width: 100%" role="progressbar"></div>
                            </div>
                            <small id="sessionTime" class="text-light">30:00 remaining</small>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <div>
                        <h1 class="h2 text-dark">
                            <i class="fas fa-tachometer-alt text-primary"></i> Staff Dashboard
                        </h1>
                        <p class="text-muted mb-0">Welcome back, <?php echo e($_SESSION['name'] ?? 'User'); ?>!</p>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="?debug=1" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-bug me-1"></i>Debug Mode
                        </a>
                        <a href="staff_dashboard.php" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="fas fa-sync-alt me-1"></i>Normal Mode
                        </a>
                        <button id="toggleTimer" class="btn btn-sm btn-outline-warning ms-2">
                            <i class="fas fa-clock me-1"></i>Show Timer
                        </button>
                    </div>
                </div>

                <!-- Success Message -->
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Dashboard Loaded Successfully!</strong> Your session is active and database is connected.
                    <span id="autoLogoutInfo" class="fst-italic">
                        (Auto-logout after 30 minutes of inactivity or browser close)
                    </span>
                </div>

                <!-- User Information Card -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Your Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> <?php echo e($_SESSION['name'] ?? 'Not set'); ?></p>
                                <p><strong>Email:</strong> <?php echo e($_SESSION['email'] ?? 'Not set'); ?></p>
                                <p><strong>User ID:</strong> <?php echo e($_SESSION['user_id'] ?? 'Not set'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Role:</strong> 
                                    <span class="badge bg-<?php echo ($_SESSION['role'] == 'admin') ? 'danger' : 'success'; ?>">
                                        <?php echo e(ucfirst($_SESSION['role'] ?? 'user')); ?>
                                    </span>
                                </p>
                                <p><strong>Session Status:</strong> 
                                    <span class="badge bg-success">Active</span>
                                </p>
                                <p><strong>Session Lifetime:</strong> 
                                    <span id="sessionLifetime" class="badge bg-info">
                                        <?php echo e(isset($_SESSION['last_activity']) ? floor((time() - $_SESSION['last_activity'])/60) : '0'); ?> min
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Auto-Logout Settings Card -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security & Auto-Logout Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-clock text-primary me-2"></i>Session Timeout</h6>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <small>Time until auto-logout:</small>
                                        <small id="timeRemaining">30 minutes</small>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div id="timeoutProgress" class="progress-bar bg-success" 
                                             style="width: 100%" role="progressbar"></div>
                                    </div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enableTimer" checked>
                                    <label class="form-check-label" for="enableTimer">Show countdown timer</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-laptop text-warning me-2"></i>Browser Close Protection</h6>
                                <p class="mb-2">Your session will automatically end when you:</p>
                                <ul class="mb-3">
                                    <li>Close the browser tab or window</li>
                                    <li>Remain inactive for 30 minutes</li>
                                    <li>Manually logout</li>
                                </ul>
                                <div class="alert alert-warning small mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    For security, always logout when using shared computers
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pharmacy Information (if available) -->
                <?php if ($pharmacy): ?>
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-clinic-medical me-2"></i>Pharmacy Information</h5>
                    </div>
                    <div class="card-body">
                        <h4><?php echo e($pharmacy['pharmacy_name']); ?></h4>
                        <p class="mb-2"><strong>Address:</strong> <?php echo e($pharmacy['pharmacy_address']); ?></p>
                        <p class="mb-0"><strong>System:</strong> 
                            <span class="badge bg-<?php echo $system_status === 'legacy' ? 'warning' : 'success'; ?>">
                                <?php echo e(ucfirst($system_status)); ?> System
                            </span>
                        </p>
                    </div>
                </div>
                <?php else: ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Pharmacy Setup Needed</h5>
                    </div>
                    <div class="card-body">
                        <p>No pharmacy is currently associated with your account.</p>
                        <a href="add_pharmacy.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-1"></i>Add Pharmacy
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="add_medicine.php" class="btn btn-outline-primary w-100 py-3">
                                            <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                                            Add Medicine
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="manage_medicines.php" class="btn btn-outline-success w-100 py-3">
                                            <i class="fas fa-pills fa-2x mb-2"></i><br>
                                            Manage Medicines
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="inventory.php" class="btn btn-outline-warning w-100 py-3">
                                            <i class="fas fa-boxes fa-2x mb-2"></i><br>
                                            View Inventory
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="reports.php" class="btn btn-outline-info w-100 py-3">
                                            <i class="fas fa-chart-bar fa-2x mb-2"></i><br>
                                            Reports
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <strong>Database</strong><br>
                                            Connection successful
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <strong>Session</strong><br>
                                            Active and valid
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="alert alert-<?php echo $pharmacy ? 'success' : 'warning'; ?>">
                                            <i class="fas fa-<?php echo $pharmacy ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                            <strong>Pharmacy</strong><br>
                                            <?php echo $pharmacy ? 'Associated' : 'Not configured'; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="alert alert-info">
                                            <i class="fas fa-shield-alt me-2"></i>
                                            <strong>Auto-Logout</strong><br>
                                            Active (30 min)
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-rocket me-2"></i>Next Steps</h5>
                            </div>
                            <div class="card-body">
                                <ol>
                                    <li><strong>Test all links</strong> in the sidebar to ensure they work</li>
                                    <li><strong>Add a pharmacy</strong> if you haven't already</li>
                                    <li><strong>Test the login/logout</strong> process</li>
                                    <li><strong>Check other dashboard pages</strong> (admin_dashboard.php, etc.)</li>
                                    <li><strong>Test auto-logout</strong> by closing browser or waiting 30 minutes</li>
                                </ol>
                                <div class="mt-3">
                                    <a href="?debug=1" class="btn btn-outline-info me-2">
                                        <i class="fas fa-bug me-1"></i>Enable Debug
                                    </a>
                                    <a href="login.php" class="btn btn-outline-secondary me-2">
                                        <i class="fas fa-sign-in-alt me-1"></i>Test Login
                                    </a>
                                    <button onclick="simulateInactivity()" class="btn btn-outline-warning me-2">
                                        <i class="fas fa-hourglass-end me-1"></i>Test Timeout
                                    </button>
                                    <a href="logout.php" class="btn btn-outline-danger" onclick="sendLogoutSignal()">
                                        <i class="fas fa-sign-out-alt me-1"></i>Test Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5 py-3 border-top text-center">
        <div class="container">
            <small class="text-muted">
                MyPharmaV1 Pharmacy System | PHP <?php echo phpversion(); ?> | 
                <a href="?debug=1" class="text-decoration-none">Debug Mode</a> | 
                <a href="staff_dashboard.php" class="text-decoration-none">Normal Mode</a> |
                <span id="sessionStatus" class="text-success">Session Active</span>
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ============================================
        // AUTO LOGOUT FUNCTIONALITY
        // ============================================
        
        // Session timeout settings (30 minutes = 1800 seconds)
        const SESSION_TIMEOUT = 1800;
        let lastActivity = Date.now();
        let inactivityTimer;
        
        // Function to send logout signal to server
        function sendLogoutSignal() {
            // Use sendBeacon if available (works best on browser close)
            if (navigator.sendBeacon) {
                navigator.sendBeacon('logout.php');
            } else {
                // Fallback for older browsers
                const xhr = new XMLHttpRequest();
                xhr.open('GET', 'logout.php', false); // Synchronous is okay here
                try {
                    xhr.send();
                } catch (e) {
                    // Silently handle errors
                }
            }
            return true;
        }
        
        // Function to update user activity
        function updateActivity() {
            lastActivity = Date.now();
            updateTimerDisplay();
            
            // Also send heartbeat to server to keep session alive
            fetch('keepalive.php?' + new Date().getTime())
                .catch(() => {
                    // Silently handle errors
                });
        }
        
        // Function to update countdown timer display
        function updateTimerDisplay() {
            const now = Date.now();
            const elapsedSeconds = Math.floor((now - lastActivity) / 1000);
            const remainingSeconds = SESSION_TIMEOUT - elapsedSeconds;
            
            if (remainingSeconds <= 0) {
                // Session expired - redirect to login
                window.location.href = 'login.php?error=session_expired';
                return;
            }
            
            const minutes = Math.floor(remainingSeconds / 60);
            const seconds = remainingSeconds % 60;
            
            // Update countdown display
            document.getElementById('countdown').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            // Update progress bar
            const progressPercent = (remainingSeconds / SESSION_TIMEOUT) * 100;
            document.getElementById('sessionProgress').style.width = `${progressPercent}%`;
            document.getElementById('timeoutProgress').style.width = `${progressPercent}%`;
            
            // Change color based on remaining time
            let progressBar = document.getElementById('sessionProgress');
            let timeoutBar = document.getElementById('timeoutProgress');
            
            if (remainingSeconds < 300) { // Less than 5 minutes
                progressBar.className = 'progress-bar bg-danger';
                timeoutBar.className = 'progress-bar bg-danger';
                document.getElementById('sessionTime').textContent = `${minutes}:${seconds.toString().padStart(2, '0')} remaining (LOW)`;
            } else if (remainingSeconds < 600) { // Less than 10 minutes
                progressBar.className = 'progress-bar bg-warning';
                timeoutBar.className = 'progress-bar bg-warning';
                document.getElementById('sessionTime').textContent = `${minutes}:${seconds.toString().padStart(2, '0')} remaining`;
            } else {
                progressBar.className = 'progress-bar bg-success';
                timeoutBar.className = 'progress-bar bg-success';
                document.getElementById('sessionTime').textContent = `${minutes} minutes remaining`;
            }
            
            // Update time remaining text
            document.getElementById('timeRemaining').textContent = 
                `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
            // Update session lifetime display
            const lifetimeMinutes = Math.floor(elapsedSeconds / 60);
            document.getElementById('sessionLifetime').textContent = `${lifetimeMinutes} min`;
        }
        
        // Function to simulate inactivity for testing
        function simulateInactivity() {
            if (confirm('Simulate 30 minutes of inactivity? This will log you out.')) {
                // Set last activity to 31 minutes ago
                lastActivity = Date.now() - (31 * 60 * 1000);
                updateTimerDisplay();
            }
        }
        
        // Set up event listeners for user activity
        ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(eventType => {
            document.addEventListener(eventType, updateActivity);
        });
        
        // Set up interval to update timer every second
        setInterval(updateTimerDisplay, 1000);
        
        // Initial timer update
        updateTimerDisplay();
        
        // Set up browser close detection
        window.addEventListener('beforeunload', function(event) {
            // Optional: Show confirmation message
            // Uncomment next line if you want to warn users
            // event.returnValue = 'Your session will end when you close the browser. Are you sure?';
            
            sendLogoutSignal();
        });
        
        // Extra safety: also listen for page unload
        window.addEventListener('unload', function() {
            sendLogoutSignal();
        });
        
        // Toggle timer visibility
        document.getElementById('toggleTimer').addEventListener('click', function() {
            const timer = document.getElementById('sessionTimer');
            timer.style.display = timer.style.display === 'none' ? 'block' : 'none';
            this.innerHTML = timer.style.display === 'none' 
                ? '<i class="fas fa-clock me-1"></i>Show Timer' 
                : '<i class="fas fa-eye-slash me-1"></i>Hide Timer';
        });
        
        // Enable/disable timer display
        document.getElementById('enableTimer').addEventListener('change', function() {
            const timer = document.getElementById('sessionTimer');
            const sidebarTimer = document.querySelector('.progress');
            const sidebarTime = document.getElementById('sessionTime');
            
            if (this.checked) {
                timer.style.display = 'block';
                sidebarTimer.style.display = 'block';
                sidebarTime.style.display = 'block';
            } else {
                timer.style.display = 'none';
                sidebarTimer.style.display = 'none';
                sidebarTime.style.display = 'none';
            }
        });
        
        // Simple confirmation for logout
        document.querySelectorAll('a[href="logout.php"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to logout?')) {
                    e.preventDefault();
                } else {
                    sendLogoutSignal();
                }
            });
        });
        
        // Initial activity update
        updateActivity();
    </script>
</body>
</html>
