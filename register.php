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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Check if pharmacy columns exist
    $pharmacy_columns_exist = false;
    $columns_check = $conn->query("SHOW COLUMNS FROM users LIKE 'pharmacy_name'");
    if ($columns_check && $columns_check->num_rows > 0) {
        $pharmacy_columns_exist = true;
    }
    
    if ($pharmacy_columns_exist) {
        $pharmacy_name = $_POST['pharmacy_name'];
        $pharmacy_address = $_POST['pharmacy_address'];
        $pharmacy_lat = $_POST['pharmacy_lat'];
        $pharmacy_lng = $_POST['pharmacy_lng'];
        $pharmacy_contact = $_POST['pharmacy_contact'];
    }
    
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
                
                // Insert user into database with 'pending' role and pharmacy information
                if ($pharmacy_columns_exist && !empty($pharmacy_name) && !empty($pharmacy_address) && !empty($pharmacy_lat) && !empty($pharmacy_lng) && !empty($pharmacy_contact)) {
                    // User is registering as a pharmacy owner
                    $insertStmt = $conn->prepare("INSERT INTO users (name, email, password, role, pharmacy_name, pharmacy_address, pharmacy_lat, pharmacy_lng, pharmacy_contact) VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?)");
                    $insertStmt->bind_param("ssssssdds", $name, $email, $hashed_password, $pharmacy_name, $pharmacy_address, $pharmacy_lat, $pharmacy_lng, $pharmacy_contact);
                } else {
                    // Regular user registration
                    $insertStmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'pending')");
                    $insertStmt->bind_param("sss", $name, $email, $hashed_password);
                }
                
                if ($insertStmt->execute()) {
                    $success = "Registration successful! Your account is pending approval by an administrator. You will be notified once approved.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
                $insertStmt->close();
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
    <title>Register - MyPharma</title>
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
                        <h2 class="h5">Create an Account</h2>
                        <p class="text-muted">Join our medicine finder community</p>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
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
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Register
                            </button>
                        </div>
                        
                        <?php
                        // Check if pharmacy columns exist
                        $pharmacy_columns_exist = false;
                        $columns_check = $conn->query("SHOW COLUMNS FROM users LIKE 'pharmacy_name'");
                        if ($columns_check && $columns_check->num_rows > 0) {
                            $pharmacy_columns_exist = true;
                        }
                        
                        if ($pharmacy_columns_exist) {
                        ?>
                        <hr class="my-4">
                        
                        <div class="alert alert-info">
                            <h5 class="alert-heading">
                                <i class="fas fa-clinic-medical me-2"></i>Pharmacy Owner Registration
                            </h5>
                            <p class="mb-0">If you're a pharmacy owner, please provide your pharmacy details below. This information will help us locate your pharmacy on the map.</p>
                        </div>
                        
                        <div class="mb-3">
                            <label for="pharmacy_name" class="form-label">Pharmacy Name</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-clinic-medical"></i>
                                </span>
                                <input type="text" class="form-control" id="pharmacy_name" name="pharmacy_name" placeholder="e.g., Mercury Drug - Pagadian City">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="pharmacy_address" class="form-label">Pharmacy Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-map-marker-alt"></i>
                                </span>
                                <textarea class="form-control" id="pharmacy_address" name="pharmacy_address" rows="2" placeholder="Full address of your pharmacy"></textarea>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pharmacy_lat" class="form-label">Latitude</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-globe-americas"></i>
                                    </span>
                                    <input type="text" class="form-control" id="pharmacy_lat" name="pharmacy_lat" placeholder="e.g., 7.82300000">
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="pharmacy_lng" class="form-label">Longitude</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-globe-americas"></i>
                                    </span>
                                    <input type="text" class="form-control" id="pharmacy_lng" name="pharmacy_lng" placeholder="e.g., 123.43000000">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="pharmacy_contact" class="form-label">Contact Number</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="text" class="form-control" id="pharmacy_contact" name="pharmacy_contact" placeholder="e.g., 09123456789">
                            </div>
                        </div>
                        <?php } ?>
                        
                        <div class="text-center">
                            <p class="mb-0">
                                Already have an account? 
                                <a href="login.php" class="text-decoration-none">Login here</a>
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