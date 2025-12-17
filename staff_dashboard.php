<?php
session_start();
include 'db.php';

// Add missing functions with better error handling
function e($data) {
    if (is_null($data)) return '';
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function requireRole($allowed_roles = []) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: login.php');
        exit();
    }
    
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        header('Location: unauthorized.php');
        exit();
    }
    
    return true;
}

// Check if user is logged in and is staff/owner/admin
requireRole(['staff', 'owner', 'admin']);

// SECURITY CHECK: Verify user is approved by admin
$user_id = $_SESSION['user_id'];
$check_approval_stmt = $conn->prepare("SELECT status, role FROM users WHERE id = ?");
$check_approval_stmt->bind_param("i", $user_id);
$check_approval_stmt->execute();
$approval_result = $check_approval_stmt->get_result();

if ($user_data = $approval_result->fetch_assoc()) {
    if ($user_data['status'] !== 'approved') {
        // User not approved - redirect to pending approval page
        $_SESSION['approval_error'] = "Your account is pending security verification by an administrator. Please wait for approval before accessing the system.";
        header('Location: pending_approval.php');
        exit();
    }
    // User is approved, continue to dashboard
    $_SESSION['actual_role'] = $user_data['role'];
} else {
    // User not found
    header('Location: login.php');
    exit();
}
$check_approval_stmt->close();

$user_role = $_SESSION['role'];

// Debug information
$debug_info = [];
$debug_mode = isset($_GET['debug']);

// Get user's pharmacy info
$pharmacy = null;
$system_status = 'checking';

// First, check if user_pharmacies table exists
try {
    $table_check = $conn->query("SHOW TABLES LIKE 'user_pharmacies'");
    $new_system_exists = ($table_check && $table_check->num_rows > 0);
    $debug_info[] = "user_pharmacies table exists: " . ($new_system_exists ? 'Yes' : 'No');
} catch (Exception $e) {
    $new_system_exists = false;
    $debug_info[] = "Error checking user_pharmacies: " . $e->getMessage();
}

if ($new_system_exists) {
    // Try to get from new system (user_pharmacies table)
    $stmt = $conn->prepare("
        SELECT p.*, up.user_role 
        FROM pharmacies p 
        JOIN user_pharmacies up ON p.id = up.pharmacy_id 
        WHERE up.user_id = ? 
        LIMIT 1
    ");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $pharmacy = $result->fetch_assoc();
                $system_status = 'new';
                $debug_info[] = "Found pharmacy in new system: " . $pharmacy['name'];
            } else {
                $debug_info[] = "No pharmacy found in new system for user ID: " . $user_id;
            }
        } else {
            $debug_info[] = "Error executing new system query: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $debug_info[] = "Failed to prepare new system query";
    }
}

// If not found in new system, check old system
if (!$pharmacy) {
    $stmt = $conn->prepare("
        SELECT 
            pharmacy_name AS name,
            pharmacy_address AS address,
            pharmacy_lat AS lat,
            pharmacy_lng AS lng,
            pharmacy_contact AS contact
        FROM users 
        WHERE id = ? AND pharmacy_name IS NOT NULL AND pharmacy_name != ''
        LIMIT 1
    ");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $pharmacy_data = $result->fetch_assoc();
                $pharmacy_data['verified'] = 0;
                $pharmacy_data['user_role'] = $user_role;
                $pharmacy = $pharmacy_data;
                $system_status = 'legacy';
                $debug_info[] = "Found pharmacy in legacy system: " . $pharmacy['name'];
                
                if ($new_system_exists) {
                    $_SESSION['migration_notice'] = "Your pharmacy data needs to be migrated to the new system. Please contact administrator.";
                }
            } else {
                $debug_info[] = "No pharmacy found in legacy system for user ID: " . $user_id;
            }
        }
        $stmt->close();
    }
}

// Get medicine statistics for the specific pharmacy
$total_medicines = 0;
$in_stock_count = 0;
$low_stock_count = 0;
$out_of_stock_count = 0;

if ($pharmacy) {
    // Check if pharmacy_medicines table exists
    try {
        $pharmacy_medicines_table_exists = $conn->query("SHOW TABLES LIKE 'pharmacy_medicines'");
        $debug_info[] = "pharmacy_medicines table exists: " . ($pharmacy_medicines_table_exists && $pharmacy_medicines_table_exists->num_rows > 0 ? 'Yes' : 'No');
        
        if ($pharmacy_medicines_table_exists && $pharmacy_medicines_table_exists->num_rows > 0) {
            if ($system_status === 'new' && isset($pharmacy['id'])) {
                // Get total medicines count for this pharmacy
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM pharmacy_medicines WHERE pharmacy_id = ?");
                if ($stmt) {
                    $stmt->bind_param("i", $pharmacy['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        $total_medicines = $row['count'];
                    }
                    $stmt->close();
                }

                // Get stock status counts for this pharmacy
                $stmt = $conn->prepare("
                    SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN stock > 10 THEN 1 ELSE 0 END) as in_stock,
                        SUM(CASE WHEN stock BETWEEN 1 AND 10 THEN 1 ELSE 0 END) as low_stock,
                        SUM(CASE WHEN stock = 0 OR stock IS NULL THEN 1 ELSE 0 END) as out_of_stock
                    FROM pharmacy_medicines 
                    WHERE pharmacy_id = ?
                ");
                if ($stmt) {
                    $stmt->bind_param("i", $pharmacy['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        $in_stock_count = $row['in_stock'] ?? 0;
                        $low_stock_count = $row['low_stock'] ?? 0;
                        $out_of_stock_count = $row['out_of_stock'] ?? 0;
                    }
                    $stmt->close();
                }
            } elseif ($system_status === 'legacy') {
                // For legacy system, we don't have pharmacy-specific data in pharmacy_medicines
                // We'll need to use the medicines_inventory table
                $stmt = $conn->prepare("
                    SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN stock_quantity > 10 THEN 1 ELSE 0 END) as in_stock,
                        SUM(CASE WHEN stock_quantity BETWEEN 1 AND 10 THEN 1 ELSE 0 END) as low_stock,
                        SUM(CASE WHEN stock_quantity = 0 OR stock_quantity IS NULL THEN 1 ELSE 0 END) as out_of_stock
                    FROM medicines_inventory 
                    WHERE pharmacy_id = (SELECT id FROM pharmacies WHERE name = ?)
                ");
                if ($stmt) {
                    $stmt->bind_param("s", $pharmacy['name']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        $total_medicines = $row['total'] ?? 0;
                        $in_stock_count = $row['in_stock'] ?? 0;
                        $low_stock_count = $row['low_stock'] ?? 0;
                        $out_of_stock_count = $row['out_of_stock'] ?? 0;
                    }
                    $stmt->close();
                }
            }
        }
    } catch (Exception $e) {
        $debug_info[] = "Error checking medicines: " . $e->getMessage();
    }
}

// Get low stock alerts for the specific pharmacy
$low_stock_alerts = [];
if ($pharmacy && isset($pharmacy['id'])) {
    try {
        if ($system_status === 'new') {
            // Get low stock alerts from pharmacy_medicines table
            $stmt = $conn->prepare("
                SELECT m.brand_name as name, pm.stock as stock_quantity 
                FROM pharmacy_medicines pm
                JOIN medicines m ON pm.medicine_id = m.id
                WHERE pm.pharmacy_id = ? 
                AND pm.stock BETWEEN 1 AND 10
                ORDER BY pm.stock ASC 
                LIMIT 5
            ");
        } else {
            // Get low stock alerts from medicines_inventory table
            $stmt = $conn->prepare("
                SELECT name, stock_quantity 
                FROM medicines_inventory 
                WHERE pharmacy_id = (SELECT id FROM pharmacies WHERE name = ?)
                AND stock_quantity BETWEEN 1 AND 10
                ORDER BY stock_quantity ASC 
                LIMIT 5
            ");
        }
        
        if ($stmt) {
            if ($system_status === 'new') {
                $stmt->bind_param("i", $pharmacy['id']);
            } else {
                $stmt->bind_param("s", $pharmacy['name']);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $low_stock_alerts[] = $row;
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        $debug_info[] = "Error getting low stock alerts: " . $e->getMessage();
    }
}

// Handle migration notice
$migration_notice = '';
if (isset($_SESSION['migration_notice'])) {
    $migration_notice = $_SESSION['migration_notice'];
    unset($_SESSION['migration_notice']);
}

// System setup notice
$setup_notice = '';
if (!$new_system_exists) {
    $setup_notice = "The new pharmacy system is not yet set up. Please run the synchronization script.";
}

$debug_info[] = "Pharmacy found: " . ($pharmacy ? 'Yes' : 'No');
$debug_info[] = "System status: " . $system_status;
$debug_info[] = "Total medicines: " . $total_medicines;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        }
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .main-content {
            background-color: #f8f9fa;
        }
        .nav-link {
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .migration-alert {
            border-left: 4px solid #ffc107;
        }
        .setup-alert {
            border-left: 4px solid #dc3545;
        }
        .quick-action-btn {
            transition: all 0.3s ease;
            border: none;
            border-radius: 10px;
            padding: 15px 10px;
        }
        .quick-action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .debug-panel {
            font-family: monospace;
            font-size: 12px;
        }
        .activity-item {
            border-left: 3px solid #007bff;
            padding-left: 15px;
            margin-bottom: 15px;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                height: auto;
                margin-bottom: 20px;
            }
            .main-content {
                margin-left: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center text-white mb-4">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-user-md fa-2x"></i>
                        </div>
                        <h4>Staff Panel</h4>
                        <p class="mb-0"><?php echo e($_SESSION['name']); ?></p>
                        <small class="text-light"><?php echo ucfirst($user_role); ?></small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="staff_dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <?php if ($pharmacy): ?>
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
                        <?php if ($user_role == 'owner' || $user_role == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="staff_management.php">
                                <i class="fas fa-users-cog me-2"></i>Staff Management
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="add_pharmacy.php">
                                <i class="fas fa-plus-circle me-2"></i>Add Pharmacy
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($user_role == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link text-warning" href="sync_pharmacy_system.php">
                                <i class="fas fa-sync me-2"></i>Sync System
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-warning" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Debug Panel -->
                <?php if ($debug_mode): ?>
                <div class="card mt-3 debug-panel">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0">Debug Information</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($debug_info as $info): ?>
                            <div><?php echo e($info); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <div>
                        <h1 class="h2 text-dark">
                            <i class="fas fa-tachometer-alt text-primary"></i> Staff Dashboard
                        </h1>
                        <p class="text-muted mb-0">Welcome back, <?php echo e($_SESSION['name']); ?>!</p>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                        <?php if ($user_role == 'admin'): ?>
                        <a href="?debug=1" class="btn btn-sm btn-outline-info ms-2">
                            <i class="fas fa-bug me-1"></i>Debug
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- System Notices -->
                <?php if ($setup_notice): ?>
                    <div class="alert alert-danger setup-alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                            <div>
                                <h5 class="mb-1">System Setup Required</h5>
                                <p class="mb-2"><?php echo e($setup_notice); ?></p>
                                <?php if ($user_role == 'admin'): ?>
                                <a href="sync_pharmacy_system.php" class="btn btn-danger">
                                    <i class="fas fa-cogs me-1"></i>Run Synchronization
                                </a>
                                <?php else: ?>
                                <small>Please contact administrator to set up the system.</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($migration_notice): ?>
                    <div class="alert alert-warning migration-alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo e($migration_notice); ?>
                    </div>
                <?php endif; ?>

                <!-- Main Content -->
                <?php if (!$pharmacy): ?>
                    <!-- No Pharmacy Registered -->
                    <div class="alert alert-warning">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x me-3 text-warning"></i>
                            <div>
                                <h5 class="mb-1">No Pharmacy Registered</h5>
                                <p class="mb-2">You need to register a pharmacy before you can manage medicines and inventory.</p>
                                <a href="add_pharmacy.php" class="btn btn-primary">
                                    <i class="fas fa-clinic-medical me-1"></i>Register Pharmacy
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Getting Started Guide -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-rocket me-2"></i>Getting Started</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="d-flex mb-3">
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                    <span class="text-white fw-bold">1</span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Register Pharmacy</h6>
                                                    <p class="text-muted mb-0">Add your pharmacy details</p>
                                                </div>
                                            </div>
                                            <div class="d-flex mb-3">
                                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                    <span class="text-white fw-bold">2</span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Wait for Approval</h6>
                                                    <p class="text-muted mb-0">Admin will verify your pharmacy</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex mb-3">
                                                <div class="bg-info rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                    <span class="text-white fw-bold">3</span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Add Medicines</h6>
                                                    <p class="text-muted mb-0">Start building your inventory</p>
                                                </div>
                                            </div>
                                            <div class="d-flex">
                                                <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                    <span class="text-white fw-bold">4</span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Manage Operations</h6>
                                                    <p class="text-muted mb-0">Handle daily pharmacy operations</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-headset me-2"></i>Need Help?</h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <i class="fas fa-question-circle fa-3x text-info mb-3"></i>
                                        <p>Contact system administrator for assistance.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Pharmacy Info Card -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-clinic-medical me-2"></i>Pharmacy Information
                                <?php if ($system_status === 'legacy'): ?>
                                    <small class="ms-2">(Legacy System)</small>
                                <?php endif; ?>
                            </h5>
                            <div>
                                <span class="badge bg-<?php echo $pharmacy['verified'] ? 'success' : 'warning'; ?> me-2">
                                    <?php echo $pharmacy['verified'] ? 'Verified' : 'Pending Approval'; ?>
                                </span>
                                <span class="badge bg-<?php echo $system_status === 'new' ? 'success' : 'warning'; ?>">
                                    <?php echo $system_status === 'new' ? 'New System' : 'Legacy System'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4 class="text-primary"><?php echo e($pharmacy['name']); ?></h4>
                                    <div class="row mt-3">
                                        <div class="col-sm-6">
                                            <p class="mb-2"><strong><i class="fas fa-map-marker-alt text-danger me-2"></i>Address:</strong><br><?php echo e($pharmacy['address']); ?></p>
                                        </div>
                                        <?php if (!empty($pharmacy['contact'])): ?>
                                        <div class="col-sm-6">
                                            <p class="mb-2"><strong><i class="fas fa-phone text-success me-2"></i>Contact:</strong><br><?php echo e($pharmacy['contact']); ?></p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mb-0">
                                        <strong>Your Role:</strong> 
                                        <span class="badge bg-info"><?php echo ucfirst($pharmacy['user_role'] ?? $user_role); ?></span>
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="d-grid gap-2">
                                        <a href="add_pharmacy.php" class="btn btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i>Edit Details
                                        </a>
                                        <?php if ($system_status === 'legacy' && $new_system_exists): ?>
                                        <div class="alert alert-info mt-2">
                                            <small>
                                                <i class="fas fa-info-circle me-1"></i>
                                                Contact admin to migrate to new system
                                            </small>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card text-white bg-primary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0"><?php echo $total_medicines; ?></h3>
                                            <p class="mb-0">Total Medicines</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-pills fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card text-white bg-success">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0"><?php echo $in_stock_count; ?></h3>
                                            <p class="mb-0">In Stock</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-check-circle fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card text-white bg-warning">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0"><?php echo $low_stock_count; ?></h3>
                                            <p class="mb-0">Low Stock</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card text-white bg-danger">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0"><?php echo $out_of_stock_count; ?></h3>
                                            <p class="mb-0">Out of Stock</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-times-circle fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

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
                                            <a href="add_medicine.php" class="btn btn-outline-primary w-100 quick-action-btn">
                                                <i class="fas fa-plus-circle me-2"></i>Add Medicine
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="manage_medicines.php" class="btn btn-outline-success w-100 quick-action-btn">
                                                <i class="fas fa-pills me-2"></i>Manage Medicines
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="inventory.php" class="btn btn-outline-warning w-100 quick-action-btn">
                                                <i class="fas fa-boxes me-2"></i>View Inventory
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="reports.php" class="btn btn-outline-info w-100 quick-action-btn">
                                                <i class="fas fa-chart-bar me-2"></i>Reports
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Alerts -->
                    <?php if (!empty($low_stock_alerts)): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alerts
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($low_stock_alerts as $alert): ?>
                                        <div class="col-md-4 mb-2">
                                            <div class="alert alert-warning d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><?php echo e($alert['name']); ?></strong>
                                                    <br>
                                                    <small>Only <?php echo $alert['stock_quantity']; ?> left in stock</small>
                                                </div>
                                                <a href="manage_medicines.php" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- System Status -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Status</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span>New System Tables</span>
                                                <span class="badge bg-<?php echo $new_system_exists ? 'success' : 'danger'; ?>">
                                                    <?php echo $new_system_exists ? 'Ready' : 'Not Setup'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span>Your Data Status</span>
                                                <span class="badge bg-<?php echo $system_status === 'new' ? 'success' : 'warning'; ?>">
                                                    <?php echo $system_status === 'new' ? 'Synchronized' : 'Legacy'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span>Medicines Database</span>
                                                <span class="badge bg-<?php echo $total_medicines > 0 ? 'success' : 'secondary'; ?>">
                                                    <?php echo $total_medicines > 0 ? 'Active' : 'Empty'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (!$new_system_exists && $user_role == 'admin'): ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        The new pharmacy system is not set up. 
                                        <a href="sync_pharmacy_system.php" class="alert-link">Run synchronization now</a>.
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh dashboard every 2 minutes
        setTimeout(function() {
            window.location.reload();
        }, 120000);
    </script>
</body>
</html>