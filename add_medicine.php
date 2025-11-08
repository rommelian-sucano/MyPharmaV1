<?php
session_start();
include 'db.php';

// Add the same functions from dashboard
function e($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function requireRole($allowed_roles = []) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: login.php');
        exit();
    }
    
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        header('Location: unauthorized.php');
        exit();
    }
    
    return true;
}

requireRole(['staff', 'owner', 'admin']);

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Get user's pharmacy
$pharmacy = null;
$stmt = $conn->prepare("
    SELECT p.*, up.user_role 
    FROM pharmacies p 
    JOIN user_pharmacies up ON p.id = up.pharmacy_id 
    WHERE up.user_id = ? 
    LIMIT 1
");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $pharmacy = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$pharmacy) {
    header('Location: staff_dashboard.php');
    exit();
}

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $generic_name = $_POST['generic_name'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock_quantity = $_POST['stock_quantity'] ?? 0;
    $expiry_date = $_POST['expiry_date'] ?? '';
    $manufacturer = $_POST['manufacturer'] ?? '';
    $prescription_required = isset($_POST['prescription_required']) ? 1 : 0;

    if (!empty($name) && !empty($category)) {
        $stmt = $conn->prepare("
            INSERT INTO medicines 
            (pharmacy_id, name, generic_name, brand, category, description, price, stock_quantity, expiry_date, manufacturer, prescription_required, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        if ($stmt) {
            $stmt->bind_param("issssssdsssi", 
                $pharmacy['id'], $name, $generic_name, $brand, $category, 
                $description, $price, $stock_quantity, $expiry_date, 
                $manufacturer, $prescription_required
            );
            
            if ($stmt->execute()) {
                $success_message = "Medicine added successfully!";
                // Clear form fields
                $_POST = array();
            } else {
                $error_message = "Error adding medicine: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Database error: " . $conn->error;
        }
    } else {
        $error_message = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medicine - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar (same as dashboard) -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar" style="min-height: 100vh; background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);">
                <div class="position-sticky pt-3">
                    <div class="text-center text-white mb-4">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-user-md fa-2x"></i>
                        </div>
                        <h4>Staff Panel</h4>
                        <p class="mb-0"><?php echo e($_SESSION['name']); ?></p>
                        <small class="text-light"><?php echo ucfirst($user_role); ?></small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="staff_dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="add_medicine.php">
                                <i class="fas fa-plus-circle me-2"></i>Add Medicine
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="manage_medicines.php">
                                <i class="fas fa-pills me-2"></i>Manage Medicines
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="inventory.php">
                                <i class="fas fa-boxes me-2"></i>Inventory
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-warning" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" style="background-color: #f8f9fa; min-height: 100vh;">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-plus-circle text-primary"></i> Add New Medicine
                    </h1>
                    <a href="staff_dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>

                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo e($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo e($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-pills me-2"></i>Medicine Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Medicine Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo e($_POST['name'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="generic_name" class="form-label">Generic Name</label>
                                        <input type="text" class="form-control" id="generic_name" name="generic_name"
                                               value="<?php echo e($_POST['generic_name'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="brand" class="form-label">Brand</label>
                                        <input type="text" class="form-control" id="brand" name="brand"
                                               value="<?php echo e($_POST['brand'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Category *</label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="">Select Category</option>
                                            <option value="Tablet" <?php echo (($_POST['category'] ?? '') == 'Tablet') ? 'selected' : ''; ?>>Tablet</option>
                                            <option value="Capsule" <?php echo (($_POST['category'] ?? '') == 'Capsule') ? 'selected' : ''; ?>>Capsule</option>
                                            <option value="Syrup" <?php echo (($_POST['category'] ?? '') == 'Syrup') ? 'selected' : ''; ?>>Syrup</option>
                                            <option value="Injection" <?php echo (($_POST['category'] ?? '') == 'Injection') ? 'selected' : ''; ?>>Injection</option>
                                            <option value="Ointment" <?php echo (($_POST['category'] ?? '') == 'Ointment') ? 'selected' : ''; ?>>Ointment</option>
                                            <option value="Drops" <?php echo (($_POST['category'] ?? '') == 'Drops') ? 'selected' : ''; ?>>Drops</option>
                                            <option value="Inhaler" <?php echo (($_POST['category'] ?? '') == 'Inhaler') ? 'selected' : ''; ?>>Inhaler</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price (₹)</label>
                                        <input type="number" class="form-control" id="price" name="price" 
                                               step="0.01" min="0" value="<?php echo e($_POST['price'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="stock_quantity" class="form-label">Stock Quantity</label>
                                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity"
                                               min="0" value="<?php echo e($_POST['stock_quantity'] ?? 0); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="expiry_date" class="form-label">Expiry Date</label>
                                        <input type="date" class="form-control" id="expiry_date" name="expiry_date"
                                               value="<?php echo e($_POST['expiry_date'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="manufacturer" class="form-label">Manufacturer</label>
                                        <input type="text" class="form-control" id="manufacturer" name="manufacturer"
                                               value="<?php echo e($_POST['manufacturer'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo e($_POST['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="prescription_required" name="prescription_required" 
                                       value="1" <?php echo isset($_POST['prescription_required']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="prescription_required">Prescription Required</label>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="reset" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-redo me-1"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Add Medicine
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>