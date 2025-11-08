<?php
session_start();
include 'db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin_dashboard.php");
        exit();
    } elseif ($_SESSION['role'] == 'staff') {
        header("Location: staff_dashboard.php");
        exit();
    } else {
        header("Location: index.php");
        exit();
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Validate input
    if (!empty($email) && !empty($password)) {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Check if user is approved
                if ($user['status'] != 'approved') {
                    $error = "Your account is pending approval by an administrator. Please wait for approval before logging in.";
                } else {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Redirect based on role
                    if ($user['role'] == 'admin') {
                        header("Location: admin_dashboard.php");
                    } elseif ($user['role'] == 'staff') {
                        header("Location: staff_dashboard.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                }
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <style>
        /* Mobile-specific styles */
        @media (max-width: 768px) {
            .login-card {
                margin: 10px;
                padding: 20px !important;
            }
            
            .h3 {
                font-size: 1.5rem;
            }
            
            .h5 {
                font-size: 1.2rem;
            }
        }
        
        /* Touch device improvements */
        button, a {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
        
        .btn {
            min-height: 44px; /* Minimum touch target size */
            min-width: 44px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-card p-5">
                    <div class="text-center mb-4">
                        <h1 class="h3 mb-3 fw-bold">
                            <i class="fas fa-capsules text-primary"></i> MyPharma
                        </h1>
                        <h2 class="h5">Login to Your Account</h2>
                        <p class="text-muted">Enter your credentials to continue</p>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <p class="mb-0 text-muted">
                                Registration is by invitation only. Please contact an administrator for access.
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>