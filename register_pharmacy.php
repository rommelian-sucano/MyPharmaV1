<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Only staff and admin can register pharmacies
if ($_SESSION['role'] !== 'staff' && $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $contact = $_POST['contact'];
    
    // Validate input
    if (!empty($name) && !empty($address) && !empty($lat) && !empty($lng) && !empty($contact)) {
        // Insert pharmacy into database with verified=0 (pending approval)
        $stmt = $conn->prepare("INSERT INTO pharmacies (name, address, lat, lng, contact, verified) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("ssssss", $name, $address, $lat, $lng, $contact);
        
        if ($stmt->execute()) {
            $success = "Pharmacy registration submitted successfully! It is pending approval by an administrator.";
        } else {
            $error = "Error registering pharmacy. Please try again.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Pharmacy - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="register-card p-5 mt-5">
                    <div class="text-center mb-4">
                        <h1 class="h3 mb-3 fw-bold">
                            <i class="fas fa-clinic-medical text-primary"></i> MyPharma
                        </h1>
                        <h2 class="h5">Register New Pharmacy</h2>
                        <p class="text-muted">Submit a new pharmacy for administrator approval</p>
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
                            <label for="name" class="form-label">Pharmacy Name</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-clinic-medical"></i>
                                </span>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-map-marker-alt"></i>
                                </span>
                                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="lat" class="form-label">Latitude</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-globe-americas"></i>
                                    </span>
                                    <input type="text" class="form-control" id="lat" name="lat" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="lng" class="form-label">Longitude</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-globe-americas"></i>
                                    </span>
                                    <input type="text" class="form-control" id="lng" name="lng" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="contact" class="form-label">Contact Number</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="text" class="form-control" id="contact" name="contact" required>
                            </div>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Submit for Approval
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <p class="mb-0">
                                <a href="<?php 
                                    if ($_SESSION['role'] === 'admin') {
                                        echo 'admin_dashboard.php';
                                    } else {
                                        echo 'staff_dashboard.php';
                                    }
                                ?>" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                                </a>
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