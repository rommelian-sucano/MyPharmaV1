<?php
session_start();
include 'db.php';

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

// Get user's pharmacy and medicines
$pharmacy = null;
$medicines = [];

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
        
        // Get medicines for this pharmacy using the correct relationship
        $med_stmt = $conn->prepare("
            SELECT m.*, pm.price, pm.stock as stock_quantity, pm.expiry_date
            FROM medicines m
            JOIN pharmacy_medicines pm ON m.id = pm.medicine_id
            WHERE pm.pharmacy_id = ?
            ORDER BY m.name
        ");
        if ($med_stmt) {
            $med_stmt->bind_param("i", $pharmacy['id']);
            $med_stmt->execute();
            $med_result = $med_stmt->get_result();
            while ($row = $med_result->fetch_assoc()) {
                $medicines[] = $row;
            }
            $med_stmt->close();
        }
    }
    $stmt->close();
}

if (!$pharmacy) {
    header('Location: staff_dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Medicines - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar" style="min-height: 100vh; background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);">
                <div class="position-sticky pt-3">
                    <div class="text-center text-white mb-4">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-user-md fa-2x"></i>
                        </div>
                        <h4>Staff Panel</h4>
                        <p class="mb-0"><?php echo e($_SESSION['name']); ?></p>
                        <small class="text-light"><?php echo ucfirst($_SESSION['role']); ?></small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="staff_dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="add_medicine.php">
                                <i class="fas fa-plus-circle me-2"></i>Add Medicine
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="manage_medicines.php">
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
                        <i class="fas fa-pills text-primary"></i> Manage Medicines
                    </h1>
                    <a href="add_medicine.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i>Add New Medicine
                    </a>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Medicine List
                        </h5>
                        <span class="badge bg-light text-dark"><?php echo count($medicines); ?> medicines</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($medicines)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-pills fa-3x text-muted mb-3"></i>
                                <h5>No Medicines Found</h5>
                                <p class="text-muted">You haven't added any medicines yet.</p>
                                <a href="add_medicine.php" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-1"></i>Add Your First Medicine
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Generic Name</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Expiry</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($medicines as $medicine): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo e($medicine['name']); ?></strong>
                                            </td>
                                            <td><?php echo e($medicine['scientific_name']); ?></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo e($medicine['category']); ?></span>
                                            </td>
                                            <td>₹<?php echo number_format($medicine['price'], 2); ?></td>
                                            <td>
                                                <?php
                                                $stock_class = 'success';
                                                if ($medicine['stock_quantity'] == 0) {
                                                    $stock_class = 'danger';
                                                } elseif ($medicine['stock_quantity'] <= 10) {
                                                    $stock_class = 'warning';
                                                }
                                                ?>
                                                <span class="badge bg-<?php echo $stock_class; ?>">
                                                    <?php echo $medicine['stock_quantity']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($medicine['expiry_date'])): ?>
                                                    <?php
                                                    $expiry_date = new DateTime($medicine['expiry_date']);
                                                    $today = new DateTime();
                                                    $diff = $today->diff($expiry_date);
                                                    $days_left = $diff->days;
                                                    
                                                    $expiry_class = 'success';
                                                    if ($days_left <= 30) {
                                                        $expiry_class = 'danger';
                                                    } elseif ($days_left <= 90) {
                                                        $expiry_class = 'warning';
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?php echo $expiry_class; ?>" 
                                                          title="<?php echo $expiry_date->format('M d, Y'); ?>">
                                                        <?php echo $expiry_date->format('M Y'); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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