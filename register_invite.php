<?php
session_start();
include 'db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';
$invite_type = '';

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    $error = "Invalid invitation link.";
} else {
    $token = $_GET['token'];
    
    // Check if invitation is valid and not used
    $stmt = $conn->prepare("SELECT * FROM invitations WHERE token = ? AND used = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $error = "Invalid or expired invitation link.";
        $stmt->close();
    } else {
        $invitation = $result->fetch_assoc();
        $invite_type = $invitation['invite_type'];
        $stmt->close();
    }
}

if (empty($error) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (!empty($name) && !empty($email) && !empty($password) && !empty($confirm_password)) {
        // Check if passwords match
        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } 
        // Check if email is valid
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        }
        // Check if password is at least 6 characters
        elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        }
        else {
            // Check if email already exists
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Email address is already registered.";
                $checkStmt->close();
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // SECURITY FIX: All new users start with 'pending' status for manual approval
                if ($invite_type === 'pharmacy_owner') {
                    // Pharmacy owner registration
                    $pharmacy_name = $_POST['pharmacy_name'];
                    $pharmacy_address = $_POST['pharmacy_address'];
                    $pharmacy_lat = $_POST['pharmacy_lat'];
                    $pharmacy_lng = $_POST['pharmacy_lng'];
                    $pharmacy_contact = $_POST['pharmacy_contact'];
                    
                    if (empty($pharmacy_name) || empty($pharmacy_address) || empty($pharmacy_lat) || empty($pharmacy_lng) || empty($pharmacy_contact)) {
                        $error = "Please provide complete pharmacy information.";
                    } else {
                        // Create user with 'pending' role and 'pending' status for admin approval
                        $insertStmt = $conn->prepare("INSERT INTO users (name, email, password, role, status, pharmacy_name, pharmacy_address, pharmacy_lat, pharmacy_lng, pharmacy_contact) VALUES (?, ?, ?, 'pending', 'pending', ?, ?, ?, ?, ?, ?)");
                        $insertStmt->bind_param("ssssssssss", $name, $email, $hashed_password, $pharmacy_name, $pharmacy_address, $pharmacy_lat, $pharmacy_lng, $pharmacy_contact);
                    }
                } else {
                    // Staff member registration - create with 'pending' role and 'pending' status
                    $insertStmt = $conn->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'pending', 'pending')");
                    $insertStmt->bind_param("sss", $name, $email, $hashed_password);
                }
                
                if (isset($insertStmt) && $insertStmt->execute()) {
                    // Mark invitation as used
                    $updateStmt = $conn->prepare("UPDATE invitations SET used = 1, used_by = ?, used_at = NOW() WHERE token = ?");
                    $user_id = $conn->insert_id;
                    $updateStmt->bind_param("is", $user_id, $token);
                    $updateStmt->execute();
                    $updateStmt->close();
                    
                    $success = "Registration successful! Your account is pending security verification by an administrator. You will be notified once approved.";
                } elseif (isset($insertStmt)) {
                    $error = "Registration failed. Please try again.";
                    $insertStmt->close();
                }
            }
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation Registration - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="register-card p-5">
                    <div class="text-center mb-4">
                        <h1 class="h3 mb-3 fw-bold">
                            <i class="fas fa-capsules text-primary"></i> MyPharma
                        </h1>
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php elseif (!empty($success)): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                            </div>
                        <?php else: ?>
                            <h2 class="h5">
                                <?php echo ($invite_type === 'pharmacy_owner') ? 'Pharmacy Owner Registration' : 'Staff Member Registration'; ?>
                            </h2>
                            <p class="text-muted">Complete your registration using your invitation</p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (empty($success)): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="form-text">Password must be at least 6 characters long.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                            
                            <?php if ($invite_type === 'pharmacy_owner'): ?>
                                <hr class="my-4">
                                
                                <div class="alert alert-info">
                                    <h5 class="alert-heading">
                                        <i class="fas fa-clinic-medical me-2"></i>Pharmacy Information
                                    </h5>
                                    <p class="mb-0">Please provide your pharmacy details below.</p>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="pharmacy_name" class="form-label">Pharmacy Name *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-clinic-medical"></i>
                                        </span>
                                        <input type="text" class="form-control" id="pharmacy_name" name="pharmacy_name" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="pharmacy_address" class="form-label">Pharmacy Address *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </span>
                                        <textarea class="form-control" id="pharmacy_address" name="pharmacy_address" rows="2" required></textarea>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="pharmacy_lat" class="form-label">Latitude *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-globe-americas"></i>
                                            </span>
                                            <input type="text" class="form-control" id="pharmacy_lat" name="pharmacy_lat" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="pharmacy_lng" class="form-label">Longitude *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-globe-americas"></i>
                                            </span>
                                            <input type="text" class="form-control" id="pharmacy_lng" name="pharmacy_lng" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="pharmacy_contact" class="form-label">Contact Number *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-phone"></i>
                                        </span>
                                        <input type="text" class="form-control" id="pharmacy_contact" name="pharmacy_contact" required>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Complete Registration
                                </button>
                            </div>
                            
                            <div class="text-center">
                                <p class="mb-0">
                                    Already have an account? 
                                    <a href="login.php" class="text-decoration-none">Login here</a>
                                </p>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="text-center">
                            <a href="login.php" class="btn btn-primary">Go to Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>