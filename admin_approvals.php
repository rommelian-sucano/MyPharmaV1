<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle approval/rejection actions
$message = '';
$error = '';

// Approve user
if (isset($_POST['approve_user'])) {
    $user_id = $_POST['user_id'];
    
    // First, get the user's current information
    $getUserStmt = $conn->prepare("SELECT role, pharmacy_name, status FROM users WHERE id = ?");
    $getUserStmt->bind_param("i", $user_id);
    $getUserStmt->execute();
    $getUserResult = $getUserStmt->get_result();
    
    if ($user = $getUserResult->fetch_assoc()) {
        // Check if user is already approved
        if ($user['status'] == 'approved') {
            $error = "User is already approved.";
        } else {
            // CORRECTED: Only update status, not role
            $stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    // Determine what role they have for the success message
                    $user_role = $user['role'];
                    if ($user_role == 'pending') {
                        // If role was pending, determine what it should be
                        $assigned_role = (!empty($user['pharmacy_name'])) ? 'owner' : 'staff';
                        $message = "User approved successfully as " . ucfirst($assigned_role) . ". They can now access the system.";
                    } else {
                        $message = "User approved successfully as " . ucfirst($user_role) . ". They can now access the system.";
                    }
                } else {
                    $error = "Error updating user.";
                }
            } else {
                $error = "Error approving user: " . $conn->error;
            }
            $stmt->close();
        }
    } else {
        $error = "User not found.";
    }
    $getUserStmt->close();
}

// Simple force approval
if (isset($_POST['simple_approve'])) {
    $email = $_POST['simple_approve_email'];
    
    $stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE email = ?");
    $stmt->bind_param("s", $email);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $message = "User forcibly approved successfully!";
        } else {
            $error = "No user found with that email.";
        }
    } else {
        $error = "Error force approving user: " . $conn->error;
    }
    $stmt->close();
}

// Reject user
if (isset($_POST['reject_user'])) {
    $user_id = $_POST['user_id'];
    
    $stmt = $conn->prepare("UPDATE users SET status = 'rejected' WHERE id = ? AND status = 'pending'");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $message = "User rejected successfully.";
        } else {
            $error = "User not found or already processed.";
        }
    } else {
        $error = "Error rejecting user: " . $conn->error;
    }
    $stmt->close();
}

// Get all users with their approval status
$stmt = $conn->prepare("SELECT id, name, email, role, pharmacy_name, status, created_at FROM users WHERE role != 'admin' ORDER BY created_at DESC");
$stmt->execute();
$all_users_result = $stmt->get_result();
$all_users = [];
while ($row = $all_users_result->fetch_assoc()) {
    $all_users[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Approvals - MyPharma Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center text-white mb-4">
                        <h4><i class="fas fa-user-shield"></i> Admin Panel</h4>
                        <p class="mb-0"><?php echo htmlspecialchars($_SESSION['name']); ?></p>
                        <small class="text-muted">Administrator</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="admin_dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="#">
                                <i class="fas fa-user-check me-2"></i>User Approvals
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
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-user-check"></i> User Security Verification
                    </h1>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-id-card"></i> Security Verification Center
                        </h5>
                        <p class="mb-0"><small>All users must be verified by admin before accessing the system</small></p>
                    </div>
                    <div class="card-body">
                        <?php if (count($all_users) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role Requested</th>
                                            <th>User Type</th>
                                            <th>Pharmacy</th>
                                            <th>Registration Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_users as $user): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <?php if ($user['role'] == 'pending'): ?>
                                                        <span class="badge bg-secondary">Pending Assignment</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info"><?php echo ucfirst($user['role']); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($user['pharmacy_name'])): ?>
                                                        <span class="badge bg-success">Pharmacy Owner</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-primary">Staff Member</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo !empty($user['pharmacy_name']) ? htmlspecialchars(substr($user['pharmacy_name'], 0, 20) . (strlen($user['pharmacy_name']) > 20 ? '...' : '')) : '-'; ?>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                                <td>
                                                    <?php if ($user['status'] == 'approved'): ?>
                                                        <span class="badge bg-success">Approved <i class="fas fa-check"></i></span>
                                                    <?php elseif ($user['status'] == 'rejected'): ?>
                                                        <span class="badge bg-danger">Rejected <i class="fas fa-times"></i></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Pending Verification <i class="fas fa-clock"></i></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($user['status'] == 'pending'): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                            <button type="submit" name="approve_user" class="btn btn-success btn-sm me-1" 
                                                                    onclick="return confirm('Verify and approve this user?\n<?php echo !empty($user['pharmacy_name']) ? 'They will be assigned the Owner role.' : 'They will be assigned the Staff role.'; ?>\nAfter approval, they can access the system.')">
                                                                <i class="fas fa-user-check"></i> Verify & Approve
                                                            </button>
                                                            <button type="submit" name="reject_user" class="btn btn-danger btn-sm" 
                                                                    onclick="return confirm('Reject this user?\nThis action cannot be undone.')">
                                                                <i class="fas fa-user-times"></i> Reject
                                                            </button>
                                                        </form>
                                                        
                                                        <!-- ADD THIS SIMPLE APPROVAL FORM -->
                                                        <form method="POST" class="d-inline ms-1">
                                                            <input type="hidden" name="simple_approve_email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                                            <button type="submit" name="simple_approve" class="btn btn-warning btn-sm" 
                                                                    onclick="return confirm('Force approve this user?\nThis will directly set their status to approved.')">
                                                                <i class="fas fa-bolt"></i> Force Approve
                                                            </button>
                                                        </form>
                                                    <?php elseif ($user['status'] == 'approved'): ?>
                                                        <span class="text-success"><i class="fas fa-check-circle"></i> Verified</span>
                                                    <?php else: ?>
                                                        <span class="text-danger"><i class="fas fa-ban"></i> Rejected</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5>No users found</h5>
                                <p class="text-muted">There are no registered users in the system.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-shield-alt"></i> Security Protocol
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <h6><i class="fas fa-user-clock text-warning"></i> Pending Verification</h6>
                                    <p>Users must wait for admin verification before accessing the system.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <h6><i class="fas fa-user-check text-success"></i> Approved Access</h6>
                                    <p>Verified users can access staff dashboard and system features.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <h6><i class="fas fa-user-times text-danger"></i> Rejected Access</h6>
                                    <p>Rejected users cannot access the system and should contact admin.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>