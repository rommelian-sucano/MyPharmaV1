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
    
    $stmt = $conn->prepare("UPDATE users SET status = 'approved', role = CASE WHEN role = 'pending' THEN 'staff' ELSE role END WHERE id = ? AND status = 'pending'");
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
        $error = "Error rejecting user.";
    }
    $stmt->close();
}

// Get pending users
$stmt = $conn->prepare("SELECT id, name, email, role, created_at FROM users WHERE status = 'pending' ORDER BY created_at DESC");
$stmt->execute();
$pending_users_result = $stmt->get_result();
$pending_users = [];
while ($row = $pending_users_result->fetch_assoc()) {
    $pending_users[] = $row;
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
                        <i class="fas fa-user-check"></i> User Approvals
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
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-clock"></i> Pending User Approvals
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($pending_users) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Registration Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_users as $user): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-warning">
                                                        <?php echo ucfirst($user['role']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                                <td>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" name="approve_user" class="btn btn-success btn-sm me-1" 
                                                                onclick="return confirm('Approve this user?')">
                                                            <i class="fas fa-check"></i> Approve
                                                        </button>
                                                        <button type="submit" name="reject_user" class="btn btn-danger btn-sm" 
                                                                onclick="return confirm('Reject this user?')">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-user-clock fa-3x text-muted mb-3"></i>
                                <h5>No pending user approvals</h5>
                                <p class="text-muted">All users are approved or there are no registration requests.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>