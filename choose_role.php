<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user info
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT role, status FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Check if user is already assigned a role
if ($user['role'] != 'pending') {
    // Redirect to appropriate dashboard
    if ($user['role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } elseif ($user['role'] == 'staff') {
        header("Location: staff_dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

// Check if user is approved
if ($user['status'] != 'approved') {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Handle role selection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_role = $_POST['role'];
    
    if ($selected_role == 'staff' || $selected_role == 'owner') {
        // Update user role
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $selected_role, $user_id);
        if ($stmt->execute()) {
            // Update session
            $_SESSION['role'] = $selected_role;
            
            // Redirect to appropriate dashboard
            if ($selected_role == 'staff') {
                header("Location: staff_dashboard.php");
            } else {
                // For owner, redirect to staff dashboard as well since we're simplifying
                header("Location: staff_dashboard.php");
            }
            exit();
        } else {
            $error = "Error updating role. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "Please select a valid role.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Role - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card mt-5">
                    <div class="card-header text-center">
                        <h2><i class="fas fa-user-tag"></i> Select Your Role</h2>
                        <p class="text-muted">Please select your role to continue</p>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label">Please select your role:</label>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="role" id="staff" value="staff" required>
                                    <label class="form-check-label" for="staff">
                                        <i class="fas fa-user-md me-2"></i>Staff Member
                                        <div class="form-text">Manage pharmacy inventory and prices</div>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="role" id="owner" value="owner" required>
                                    <label class="form-check-label" for="owner">
                                        <i class="fas fa-user-tie me-2"></i>Pharmacy Owner
                                        <div class="form-text">Manage pharmacy details and staff</div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check-circle me-2"></i>Continue
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>