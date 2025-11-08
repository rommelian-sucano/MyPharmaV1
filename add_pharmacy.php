<?php
session_start();
include 'db.php';
include 'auth.php';

// Check if user is logged in and is staff/owner/admin
requireRole(['staff', 'owner', 'admin']);

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$message = '';
$error = '';

// Check if user already has a pharmacy in NEW system
$existing_pharmacy = null;
$stmt = $conn->prepare("
    SELECT p.* FROM pharmacies p 
    JOIN user_pharmacies up ON p.id = up.pharmacy_id 
    WHERE up.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $existing_pharmacy = $result->fetch_assoc();
}
$stmt->close();

// Also check in old system
if (!$existing_pharmacy) {
    $stmt = $conn->prepare("
        SELECT pharmacy_name, pharmacy_address 
        FROM users 
        WHERE id = ? AND pharmacy_name IS NOT NULL AND pharmacy_name != ''
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $existing_pharmacy = $result->fetch_assoc();
    }
    $stmt->close();
}

// Handle pharmacy details submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pharmacy_name = trim($_POST['pharmacy_name']);
    $pharmacy_address = trim($_POST['pharmacy_address']);
    $pharmacy_contact = trim($_POST['pharmacy_contact'] ?? '');
    
    if (!empty($pharmacy_name) && !empty($pharmacy_address)) {
        
        // Check if pharmacy already exists
        $check_stmt = $conn->prepare("SELECT id FROM pharmacies WHERE name = ? AND address = ?");
        $check_stmt->bind_param("ss", $pharmacy_name, $pharmacy_address);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            // Insert into pharmacies table (NEW SYSTEM)
            $stmt = $conn->prepare("INSERT INTO pharmacies (name, address, contact, verified, created_at) VALUES (?, ?, ?, 0, NOW())");
            $stmt->bind_param("sss", $pharmacy_name, $pharmacy_address, $pharmacy_contact);
            
            if ($stmt->execute()) {
                $pharmacy_id = $stmt->insert_id;
                
                // Link user to pharmacy in user_pharmacies table
                $link_stmt = $conn->prepare("INSERT INTO user_pharmacies (user_id, pharmacy_id, user_role) VALUES (?, ?, ?)");
                $user_role = ($_SESSION['role'] == 'owner') ? 'owner' : 'staff';
                $link_stmt->bind_param("iis", $user_id, $pharmacy_id, $user_role);
                
                if ($link_stmt->execute()) {
                    $message = "Pharmacy details submitted successfully. Waiting for admin approval.";
                    
                    // Add notification for admin
                    addNotification($conn, "New pharmacy registration: $pharmacy_name", 'info');
                    
                    // Update old system if exists
                    $update_stmt = $conn->prepare("UPDATE users SET pharmacy_name = ?, pharmacy_address = ?, pharmacy_contact = ? WHERE id = ?");
                    $update_stmt->bind_param("sssi", $pharmacy_name, $pharmacy_address, $pharmacy_contact, $user_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    // Redirect to staff dashboard
                    header("Location: staff_dashboard.php");
                    exit();
                } else {
                    $error = "Error linking pharmacy to user. Please try again.";
                    // Clean up the pharmacy entry
                    $conn->query("DELETE FROM pharmacies WHERE id = $pharmacy_id");
                }
                $link_stmt->close();
            } else {
                $error = "Error saving pharmacy details. Please try again.";
            }
            $stmt->close();
        } else {
            $existing = $check_result->fetch_assoc();
            // Link user to existing pharmacy
            $link_stmt = $conn->prepare("INSERT IGNORE INTO user_pharmacies (user_id, pharmacy_id, user_role) VALUES (?, ?, ?)");
            $user_role = ($_SESSION['role'] == 'owner') ? 'owner' : 'staff';
            $link_stmt->bind_param("iis", $user_id, $existing['id'], $user_role);
            
            if ($link_stmt->execute()) {
                $message = "Linked to existing pharmacy successfully.";
                
                // Update old system
                $update_stmt = $conn->prepare("UPDATE users SET pharmacy_name = ?, pharmacy_address = ?, pharmacy_contact = ? WHERE id = ?");
                $update_stmt->bind_param("sssi", $pharmacy_name, $pharmacy_address, $pharmacy_contact, $user_id);
                $update_stmt->execute();
                $update_stmt->close();
                
                header("Location: staff_dashboard.php");
                exit();
            } else {
                $error = "Error linking to existing pharmacy. Please try again.";
            }
            $link_stmt->close();
        }
        $check_stmt->close();
    } else {
        $error = "Please fill in all required fields.";
    }
}

// If user already has a pharmacy, redirect them
if ($existing_pharmacy) {
    header("Location: staff_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Pharmacy - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card mt-5">
                    <div class="card-header text-center bg-primary text-white">
                        <h2><i class="fas fa-clinic-medical"></i> Register Pharmacy</h2>
                        <p class="mb-0">Synchronized System</p>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo e($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?php echo e($message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="pharmacy_name" class="form-label">
                                    <i class="fas fa-signature me-1"></i>Pharmacy Name *
                                </label>
                                <input type="text" class="form-control" id="pharmacy_name" name="pharmacy_name" 
                                       value="<?php echo isset($_POST['pharmacy_name']) ? e($_POST['pharmacy_name']) : ''; ?>" 
                                       required placeholder="Enter pharmacy name">
                            </div>
                            
                            <div class="mb-3">
                                <label for="pharmacy_address" class="form-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>Pharmacy Address *
                                </label>
                                <textarea class="form-control" id="pharmacy_address" name="pharmacy_address" 
                                          rows="3" required placeholder="Enter full pharmacy address"><?php echo isset($_POST['pharmacy_address']) ? e($_POST['pharmacy_address']) : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="pharmacy_contact" class="form-label">
                                    <i class="fas fa-phone me-1"></i>Contact Information
                                </label>
                                <input type="text" class="form-control" id="pharmacy_contact" name="pharmacy_contact" 
                                       value="<?php echo isset($_POST['pharmacy_contact']) ? e($_POST['pharmacy_contact']) : ''; ?>"
                                       placeholder="Phone number or email">
                                <div class="form-text">Provide phone number or email for contact purposes</div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Submit for Approval
                                </button>
                                <a href="staff_dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a>
                            </div>
                            
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Your pharmacy will be reviewed by an administrator before it becomes active.
                                    This will sync with both new and legacy systems.
                                </small>
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