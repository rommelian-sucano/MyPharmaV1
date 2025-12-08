<?php
// ============================================
// AUTO LOGOUT CONFIGURATION
// ============================================
// Set session cookie to expire when browser closes
ini_set('session.cookie_lifetime', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', false); // Set to true if using HTTPS
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

include 'db.php';
include 'auth.php';

// Check if user is logged in and is admin
requireRole(['admin']);

// Get pending users count
$pending_users_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'pending'");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $pending_users_count = $row['count'];
    }
    $stmt->close();
}

// Initialize variables
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Approve pharmacy
    if (isset($_POST['approve_pharmacy'])) {
        $pharmacy_id = $_POST['pharmacy_id'];
        
        $stmt = $conn->prepare("UPDATE pharmacies SET verified = 1 WHERE id = ?");
        $stmt->bind_param("i", $pharmacy_id);
        if ($stmt->execute()) {
            $message = "Pharmacy approved successfully.";
        } else {
            $error = "Error approving pharmacy.";
        }
        $stmt->close();
    }

    // Reject pharmacy
    if (isset($_POST['reject_pharmacy'])) {
        $pharmacy_id = $_POST['pharmacy_id'];
        
        $stmt = $conn->prepare("DELETE FROM pharmacies WHERE id = ?");
        $stmt->bind_param("i", $pharmacy_id);
        if ($stmt->execute()) {
            $message = "Pharmacy rejected successfully.";
        } else {
            $error = "Error rejecting pharmacy.";
        }
        $stmt->close();
    }

    // Approve user registration
    if (isset($_POST['approve_user'])) {
        $user_id = $_POST['user_id'];
        
        $stmt = $conn->prepare("UPDATE users SET role = 'staff' WHERE id = ? AND role = 'pending'");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $message = "User approved successfully.";
            } else {
                $error = "User not found or already processed.";
            }
        } else {
            $error = "Error approving user.";
        }
        $stmt->close();
    }

    // Reject user registration
    if (isset($_POST['reject_user'])) {
        $user_id = $_POST['user_id'];
        
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'pending'");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $message = "User registration rejected successfully.";
            } else {
                $error = "User not found or already processed.";
            }
        } else {
            $error = "Error rejecting user registration.";
        }
        $stmt->close();
    }
}

// Get pending pharmacies
$stmt = $conn->prepare("SELECT * FROM pharmacies WHERE verified = 0 ORDER BY name");
$stmt->execute();
$pending_pharmacies_result = $stmt->get_result();
$pending_pharmacies = [];
while ($row = $pending_pharmacies_result->fetch_assoc()) {
    $pending_pharmacies[] = $row;
}
$stmt->close();

// Get verified pharmacies
$stmt = $conn->prepare("SELECT * FROM pharmacies WHERE verified = 1 ORDER BY name");
$stmt->execute();
$verified_pharmacies_result = $stmt->get_result();
$verified_pharmacies = [];
while ($row = $verified_pharmacies_result->fetch_assoc()) {
    $verified_pharmacies[] = $row;
}
$stmt->close();

// Get all users
$stmt = $conn->prepare("SELECT id, name, email, role FROM users ORDER BY role, name");
$stmt->execute();
$users_result = $stmt->get_result();
$users = [];
while ($row = $users_result->fetch_assoc()) {
    $row['status'] = ($row['role'] == 'pending') ? 'pending' : 'approved';
    $users[] = $row;
}
$stmt->close();

// Get pending user registrations
$stmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE role = 'pending' ORDER BY created_at DESC");
$stmt->execute();
$pending_users_result = $stmt->get_result();
$pending_users = [];
while ($row = $pending_users_result->fetch_assoc()) {
    $pending_users[] = $row;
}
$stmt->close();

// Get recent notifications
$notifications = [];
try {
    $stmt = $conn->prepare("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $notifications_result = $stmt->get_result();
    while ($row = $notifications_result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    // Notifications table might not exist
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin Dashboard - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <style>
        /* Mobile-specific styles */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 10px;
            }
            
            .sidebar {
                position: relative;
                height: auto;
                margin-bottom: 20px;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .h2 {
                font-size: 1.5rem;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }
            
            .session-timer {
                bottom: 10px !important;
                right: 10px !important;
                font-size: 12px !important;
                padding: 8px 12px !important;
            }
        }
        
        /* Touch device improvements */
        button, a {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
        
        .btn {
            min-height: 44px;
            min-width: 44px;
        }
        
        .btn-admin-action {
            margin-bottom: 5px;
        }
        
        .stat-card {
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        /* Session timer styles */
        .session-timer {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.85);
            color: white;
            padding: 10px 15px;
            border-radius: 20px;
            font-size: 14px;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            display: none;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .session-timer.warning {
            background: rgba(255, 193, 7, 0.9);
            color: #212529;
        }
        
        .session-timer.danger {
            background: rgba(220, 53, 69, 0.9);
            color: white;
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        /* Security badge */
        .security-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.8rem;
        }
        
        /* Session info in sidebar */
        .session-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 10px;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>
    <!-- Session Timer -->
    <div id="sessionTimer" class="session-timer">
        <i class="fas fa-clock me-2"></i>
        <span id="countdown">30:00</span>
        <button id="hideTimer" class="btn btn-sm btn-link text-white ms-2 p-0" style="min-width: auto;">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center text-white mb-4">
                        <h4><i class="fas fa-user-shield"></i> Admin Panel</h4>
                        <p class="mb-0"><?php echo e($_SESSION['name']); ?></p>
                        <small class="text-muted">Administrator</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="#">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#pharmacies">
                                <i class="fas fa-clinic-medical me-2"></i>Pharmacies
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#users">
                                <i class="fas fa-users me-2"></i>Users
                                <?php if ($pending_users_count > 0): ?>
                                    <span class="badge bg-danger float-end"><?php echo $pending_users_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#notifications">
                                <i class="fas fa-bell me-2"></i>Notifications
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="admin_invite.php">
                                <i class="fas fa-user-plus me-2"></i>Invite Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-warning" href="logout.php" onclick="sendLogoutSignal()">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Session Information -->
                    <div class="session-info">
                        <small class="text-light d-block mb-1">Session Security</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Auto-logout:</small>
                            <small class="text-success">Active</small>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div id="sessionProgress" class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                        <small id="sessionTime" class="text-light d-block text-center mt-1">30:00</small>
                        <button id="toggleTimerBtn" class="btn btn-sm btn-outline-light w-100 mt-2">
                            <i class="fas fa-eye me-1"></i>Show Timer
                        </button>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
                <!-- Security Header -->
                <div class="alert alert-info mb-3 py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-shield-alt me-2"></i>
                            <strong>Security Active:</strong> Auto-logout enabled (30 min inactivity)
                        </div>
                        <div>
                            <span class="badge bg-success me-2">
                                <i class="fas fa-check-circle me-1"></i>Session: Active
                            </span>
                            <span class="badge bg-info">
                                <i class="fas fa-clock me-1"></i>Last Activity: 
                                <?php 
                                if (isset($_SESSION['last_activity'])) {
                                    $minutes = floor((time() - $_SESSION['last_activity']) / 60);
                                    $seconds = (time() - $_SESSION['last_activity']) % 60;
                                    echo $minutes . 'm ' . $seconds . 's ago';
                                } else {
                                    echo 'Just now';
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                    </h1>
                    <div class="btn-toolbar">
                        <div class="btn-group me-2">
                            <button id="refreshSession" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-sync-alt me-1"></i>Refresh Session
                            </button>
                            <button id="testTimeout" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-hourglass-end me-1"></i>Test Timeout
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo e($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo e($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Auto-Logout Info Card -->
                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-shield me-2"></i> Security & Auto-Logout System
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-clock text-info me-2"></i> Session Timer</h6>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Time until auto-logout:</small>
                                        <small id="timeRemaining" class="fw-bold">30 minutes</small>
                                    </div>
                                    <div class="progress" style="height: 12px;">
                                        <div id="timeoutProgress" class="progress-bar" 
                                             style="width: 100%" role="progressbar"></div>
                                    </div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enableTimer" checked>
                                    <label class="form-check-label" for="enableTimer">
                                        Show floating timer
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-lock text-success me-2"></i> Protection Features</h6>
                                <ul class="mb-3">
                                    <li>Auto-logout after 30 minutes of inactivity</li>
                                    <li>Session ends when browser closes</li>
                                    <li>Secure HTTP-only session cookies</li>
                                    <li>Activity detection (mouse, keyboard, touch)</li>
                                </ul>
                                <div class="alert alert-warning small mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Security Note:</strong> Always logout manually on shared computers
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Section -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3><?php echo count($verified_pharmacies); ?></h3>
                                <p class="mb-0">Verified Pharmacies</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-dark">
                            <div class="card-body text-center">
                                <h3><?php echo count($pending_pharmacies); ?></h3>
                                <p class="mb-0">Pending Pharmacies</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body text-center">
                                <h3><?php echo count(array_filter($users, function($user) { return $user['role'] === 'staff'; })); ?></h3>
                                <p class="mb-0">Active Staff</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body text-center">
                                <h3><?php echo $pending_users_count; ?></h3>
                                <p class="mb-0">Pending Users</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pharmacies Section -->
                <div class="row" id="pharmacies">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-clinic-medical"></i> Pharmacies Management
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Pending Pharmacies -->
                                <h6 class="mb-3 text-warning">
                                    <i class="fas fa-clock"></i> Pending Pharmacy Approvals
                                </h6>
                                <?php if (count($pending_pharmacies) === 0): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> No pending pharmacy registrations.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Address</th>
                                                    <th>Contact</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pending_pharmacies as $pharmacy): ?>
                                                    <tr>
                                                        <td><?php echo e($pharmacy['name']); ?></td>
                                                        <td><?php echo e($pharmacy['address']); ?></td>
                                                        <td><?php echo e($pharmacy['contact']); ?></td>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="pharmacy_id" value="<?php echo $pharmacy['id']; ?>">
                                                                    <button type="submit" name="approve_pharmacy" class="btn btn-sm btn-success btn-admin-action">
                                                                        <i class="fas fa-check me-1"></i>Approve
                                                                    </button>
                                                                </form>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="pharmacy_id" value="<?php echo $pharmacy['id']; ?>">
                                                                    <button type="submit" name="reject_pharmacy" class="btn btn-sm btn-danger btn-admin-action" 
                                                                            onclick="return confirm('Are you sure you want to reject this pharmacy?')">
                                                                        <i class="fas fa-times me-1"></i>Reject
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Verified Pharmacies -->
                                <h6 class="mt-4 mb-3 text-success">
                                    <i class="fas fa-check-circle"></i> Verified Pharmacies
                                </h6>
                                <?php if (count($verified_pharmacies) === 0): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> No verified pharmacies.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Address</th>
                                                    <th>Contact</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($verified_pharmacies as $pharmacy): ?>
                                                    <tr>
                                                        <td><?php echo e($pharmacy['name']); ?></td>
                                                        <td><?php echo e($pharmacy['address']); ?></td>
                                                        <td><?php echo e($pharmacy['contact']); ?></td>
                                                        <td>
                                                            <span class="badge bg-success">Verified</span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Section -->
                <div class="row" id="users">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-users"></i> Users Management
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Pending User Registrations -->
                                <h6 class="mb-3 text-warning">
                                    <i class="fas fa-user-clock"></i> Pending User Registrations
                                </h6>
                                <?php if (count($pending_users) === 0): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> No pending user registrations.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Registration Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pending_users as $user): ?>
                                                    <tr>
                                                        <td><?php echo e($user['name']); ?></td>
                                                        <td><?php echo e($user['email']); ?></td>
                                                        <td><?php echo date('M j, Y g:i A', strtotime($user['created_at'])); ?></td>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                    <button type="submit" name="approve_user" class="btn btn-sm btn-success btn-admin-action">
                                                                        <i class="fas fa-check me-1"></i>Approve
                                                                    </button>
                                                                </form>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                    <button type="submit" name="reject_user" class="btn btn-sm btn-danger btn-admin-action" 
                                                                            onclick="return confirm('Are you sure you want to reject this user registration?')">
                                                                        <i class="fas fa-times me-1"></i>Reject
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- All Users -->
                                <h6 class="mt-4 mb-3 text-primary">
                                    <i class="fas fa-list"></i> All Users
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td><?php echo e($user['name']); ?></td>
                                                    <td><?php echo e($user['email']); ?></td>
                                                    <td>
                                                        <?php if ($user['role'] === 'admin'): ?>
                                                            <span class="badge bg-danger">Admin</span>
                                                        <?php elseif ($user['role'] === 'staff'): ?>
                                                            <span class="badge bg-success">Staff</span>
                                                        <?php elseif ($user['role'] === 'pending'): ?>
                                                            <span class="badge bg-warning">Pending</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-primary"><?php echo ucfirst($user['role']); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($user['role'] === 'pending'): ?>
                                                            <span class="badge bg-warning">Pending</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success">Approved</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications Section -->
                <div class="row" id="notifications">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-bell"></i> System Notifications
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($notifications) === 0): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> No notifications.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Message</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($notifications as $notification): ?>
                                                    <tr>
                                                        <td><?php echo e($notification['message']); ?></td>
                                                        <td><?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

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
                xhr.open('GET', 'logout.php', false);
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
            
            // Send heartbeat to server to keep session alive
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
            const displayTime = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            // Update countdown display
            document.getElementById('countdown').textContent = displayTime;
            document.getElementById('sessionTime').textContent = displayTime;
            
            // Update progress bars
            const progressPercent = (remainingSeconds / SESSION_TIMEOUT) * 100;
            const sessionProgress = document.getElementById('sessionProgress');
            const timeoutProgress = document.getElementById('timeoutProgress');
            
            sessionProgress.style.width = `${progressPercent}%`;
            timeoutProgress.style.width = `${progressPercent}%`;
            
            // Update timer container class based on remaining time
            const timerContainer = document.getElementById('sessionTimer');
            
            if (remainingSeconds < 300) { // Less than 5 minutes
                timerContainer.className = 'session-timer danger';
                sessionProgress.className = 'progress-bar bg-danger';
                timeoutProgress.className = 'progress-bar bg-danger';
                document.getElementById('timeRemaining').textContent = `${minutes}:${seconds.toString().padStart(2, '0')} (LOW)`;
            } else if (remainingSeconds < 600) { // Less than 10 minutes
                timerContainer.className = 'session-timer warning';
                sessionProgress.className = 'progress-bar bg-warning';
                timeoutProgress.className = 'progress-bar bg-warning';
                document.getElementById('timeRemaining').textContent = displayTime;
            } else {
                timerContainer.className = 'session-timer';
                sessionProgress.className = 'progress-bar bg-success';
                timeoutProgress.className = 'progress-bar bg-success';
                document.getElementById('timeRemaining').textContent = `${minutes} minutes`;
            }
        }
        
        // Function to simulate inactivity for testing
        function simulateInactivity() {
            if (confirm('Simulate 30 minutes of inactivity? This will log you out.')) {
                // Set last activity to 31 minutes ago
                lastActivity = Date.now() - (31 * 60 * 1000);
                updateTimerDisplay();
            }
        }
        
        // Function to refresh session (extend time)
        function refreshSession() {
            lastActivity = Date.now();
            updateTimerDisplay();
            
            // Show feedback
            const btn = document.getElementById('refreshSession');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check me-1"></i>Refreshed';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            }, 2000);
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
            sendLogoutSignal();
        });
        
        // Extra safety: also listen for page unload
        window.addEventListener('unload', function() {
            sendLogoutSignal();
        });
        
        // DOM event handlers
        document.getElementById('toggleTimerBtn').addEventListener('click', function() {
            const timer = document.getElementById('sessionTimer');
            if (timer.style.display === 'none') {
                timer.style.display = 'block';
                this.innerHTML = '<i class="fas fa-eye-slash me-1"></i>Hide Timer';
            } else {
                timer.style.display = 'none';
                this.innerHTML = '<i class="fas fa-eye me-1"></i>Show Timer';
            }
        });
        
        document.getElementById('hideTimer').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('sessionTimer').style.display = 'none';
            document.getElementById('toggleTimerBtn').innerHTML = '<i class="fas fa-eye me-1"></i>Show Timer';
        });
        
        document.getElementById('enableTimer').addEventListener('change', function() {
            const timer = document.getElementById('sessionTimer');
            timer.style.display = this.checked ? 'block' : 'none';
            
            const toggleBtn = document.getElementById('toggleTimerBtn');
            if (!this.checked) {
                toggleBtn.innerHTML = '<i class="fas fa-eye me-1"></i>Show Timer (disabled)';
                toggleBtn.disabled = true;
            } else {
                toggleBtn.innerHTML = '<i class="fas fa-eye me-1"></i>Show Timer';
                toggleBtn.disabled = false;
            }
        });
        
        document.getElementById('refreshSession').addEventListener('click', refreshSession);
        document.getElementById('testTimeout').addEventListener('click', simulateInactivity);
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Show timer by default
        document.getElementById('sessionTimer').style.display = 'block';
        
        // Initial activity update
        updateActivity();
    </script>
</body>
</html>
