<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white text-center">
                        <h3 class="mb-0">
                            <i class="fas fa-user-lock"></i> Access Denied
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-ban fa-3x text-danger"></i>
                        </div>
                        
                        <h4 class="mb-3">Unauthorized Access</h4>
                        
                        <div class="alert alert-danger">
                            <p>You do not have permission to access this page.</p>
                            <p>This could be due to:</p>
                            <ul class="text-start">
                                <li>Insufficient user privileges</li>
                                <li>Account not approved by administrator</li>
                                <li>Session expired</li>
                            </ul>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-home"></i> Return to Home
                            </a>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="logout.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-outline-primary">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>