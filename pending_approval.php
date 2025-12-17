<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, role, status FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    $user_name = $user['name'];
    $user_email = $user['email'];
    $user_role = $user['role'];
    $user_status = $user['status'];
} else {
    // User not found
    session_destroy();
    header('Location: login.php');
    exit();
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Pending Verification - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3 class="mb-0">
                            <i class="fas fa-user-shield"></i> Security Verification Required
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-user-clock fa-2x text-warning"></i>
                        </div>
                        
                        <h4 class="mb-3">Hello, <?php echo htmlspecialchars($user_name); ?>!</h4>
                        
                        <?php if (isset($_SESSION['approval_error'])): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <?php 
                                    echo $_SESSION['approval_error'];
                                    unset($_SESSION['approval_error']);
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-info">
                            <h5><i class="fas fa-id-card"></i> Account Status</h5>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($user_email); ?></p>
                            <p><strong>Requested Role:</strong> 
                                <?php 
                                    if ($user_role == 'pending') {
                                        echo 'Pending Assignment';
                                    } else {
                                        echo ucfirst($user_role);
                                    }
                                ?>
                            </p>
                            <p><strong>Verification Status:</strong> 
                                <?php if ($user_status == 'pending'): ?>
                                    <span class="badge bg-warning">Pending Admin Verification</span>
                                <?php elseif ($user_status == 'approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <div class="border rounded p-3 mb-4">
                            <h5><i class="fas fa-shield-alt"></i> Security Protocol</h5>
                            <p class="text-muted">
                                For security reasons, all new accounts must be manually verified by an administrator 
                                before accessing the system. This process ensures only authorized personnel can 
                                access sensitive information.
                            </p>
                            <p class="text-muted">
                                Please wait for admin verification. You will receive access immediately after approval.
                            </p>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="location.reload()">
                                <i class="fas fa-sync-alt"></i> Check Verification Status
                            </button>
                            <a href="logout.php" class="btn btn-outline-secondary">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                        
                        <div class="mt-4 text-muted">
                            <small>
                                <i class="fas fa-question-circle"></i> 
                                Questions? Contact your system administrator.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh every 30 seconds to check approval status
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>