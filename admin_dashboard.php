<?php
session_start();
include 'db.php';
include 'auth.php';

// Check if user is logged in and is admin
requireRole(['admin']);

// Get pending users count - FIX for missing variable
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
    </style>
</head>
<body>
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
                            <a class="nav-link text-white" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                    </h1>
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
<!-- Pharmacy Users Relationship Section -->
<div class="row" id="relationships">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-link me-2"></i> Pharmacy - User Relationships
                </h5>
            </div>
            <div class="card-body">
                <?php
                // Get pharmacy-user relationships
                $rel_table_check = $conn->query("SHOW TABLES LIKE 'user_pharmacies'");
                $new_system_exists = ($rel_table_check && $rel_table_check->num_rows > 0);

                $relationships = [];
                if ($new_system_exists) {
                    $relationship_stmt = $conn->prepare("
                        SELECT u.id as user_id, u.name as user_name, u.email, u.role as user_role,
                               p.id as pharmacy_id, p.name as pharmacy_name, p.address, p.verified, 
                               up.user_role as pharmacy_user_role
                        FROM user_pharmacies up 
                        JOIN users u ON up.user_id = u.id 
                        JOIN pharmacies p ON up.pharmacy_id = p.id 
                        ORDER BY p.name, u.name
                    ");
                    $relationship_stmt->execute();
                    $relationships_result = $relationship_stmt->get_result();
                    while ($row = $relationships_result->fetch_assoc()) {
                        $relationships[] = $row;
                    }
                    $relationship_stmt->close();
                }

                // Get users with pharmacy data in old system
                if ($new_system_exists) {
                    $old_system_stmt = $conn->prepare("
                        SELECT id, name, email, role, 
                               pharmacy_name, pharmacy_address, pharmacy_contact
                        FROM users 
                        WHERE pharmacy_name IS NOT NULL AND pharmacy_name != ''
                          AND id NOT IN (SELECT user_id FROM user_pharmacies)
                        ORDER BY name
                    ");
                } else {
                    $old_system_stmt = $conn->prepare("
                        SELECT id, name, email, role, 
                               pharmacy_name, pharmacy_address, pharmacy_contact
                        FROM users 
                        WHERE pharmacy_name IS NOT NULL AND pharmacy_name != ''
                        ORDER BY name
                    ");
                }
                $old_system_stmt->execute();
                $old_system_result = $old_system_stmt->get_result();
                $old_system_users = [];
                while ($row = $old_system_result->fetch_assoc()) {
                    $old_system_users[] = $row;
                }
                $old_system_stmt->close();
                ?>

                <!-- New System Relationships -->
                <h6 class="mb-3 text-success">
                    <i class="fas fa-sync me-2"></i> New System Relationships
                </h6>
                <?php if (count($relationships) === 0): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No pharmacy-user relationships in new system.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>System Role</th>
                                    <th>Pharmacy</th>
                                    <th>Pharmacy Role</th>
                                    <th>Pharmacy Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($relationships as $rel): ?>
                                    <tr>
                                        <td>
                                            <?php echo e($rel['user_name']); ?>
                                            <br><small class="text-muted">ID: <?php echo $rel['user_id']; ?></small>
                                        </td>
                                        <td><?php echo e($rel['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $rel['user_role'] == 'admin' ? 'danger' : ($rel['user_role'] == 'owner' ? 'primary' : 'success'); ?>">
                                                <?php echo ucfirst($rel['user_role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo e($rel['pharmacy_name']); ?>
                                            <br><small class="text-muted"><?php echo e($rel['address']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $rel['pharmacy_user_role'] == 'owner' ? 'primary' : 'secondary'; ?>">
                                                <?php echo ucfirst($rel['pharmacy_user_role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $rel['verified'] ? 'success' : 'warning'; ?>">
                                                <?php echo $rel['verified'] ? 'Verified' : 'Pending'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        onclick="viewRelationship(<?php echo $rel['user_id']; ?>, <?php echo $rel['pharmacy_id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-warning" 
                                                        onclick="editRelationship(<?php echo $rel['user_id']; ?>, <?php echo $rel['pharmacy_id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Old System Users -->
                <h6 class="mt-4 mb-3 text-warning">
                    <i class="fas fa-database me-2"></i> Legacy System Users (Need Migration)
                </h6>
                <?php if (count($old_system_users) === 0): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> All users are migrated to the new system.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Pharmacy Name</th>
                                    <th>Pharmacy Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($old_system_users as $user): ?>
                                    <tr>
                                        <td><?php echo e($user['name']); ?></td>
                                        <td><?php echo e($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'owner' ? 'primary' : 'success'); ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo e($user['pharmacy_name']); ?></td>
                                        <td><?php echo e($user['pharmacy_address']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-success" 
                                                    onclick="migrateUser(<?php echo $user['id']; ?>)">
                                                <i class="fas fa-sync me-1"></i>Migrate
                                            </button>
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

<script>
function viewRelationship(userId, pharmacyId) {
    alert('View relationship: User ' + userId + ' - Pharmacy ' + pharmacyId);
    // Implement view functionality
}

function editRelationship(userId, pharmacyId) {
    alert('Edit relationship: User ' + userId + ' - Pharmacy ' + pharmacyId);
    // Implement edit functionality
}

function migrateUser(userId) {
    if (confirm('Migrate this user to the new system?')) {
        // AJAX call to migrate user
        fetch('migrate_user.php?user_id=' + userId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User migrated successfully!');
                    location.reload();
                } else {
                    alert('Error migrating user: ' + data.error);
                }
            });
    }
}
</script>
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
    </script>
</body>
</html>