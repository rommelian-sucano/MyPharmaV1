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

// Get user's pharmacy and inventory stats
$pharmacy = null;
$inventory_stats = [
    'total' => 0,
    'in_stock' => 0,
    'low_stock' => 0,
    'out_of_stock' => 0
];

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
        
        // Get inventory statistics using the correct relationship
        $stats_stmt = $conn->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN pm.stock > 10 THEN 1 ELSE 0 END) as in_stock,
                SUM(CASE WHEN pm.stock BETWEEN 1 AND 10 THEN 1 ELSE 0 END) as low_stock,
                SUM(CASE WHEN pm.stock = 0 OR pm.stock IS NULL THEN 1 ELSE 0 END) as out_of_stock
            FROM medicines m
            JOIN pharmacy_medicines pm ON m.id = pm.medicine_id
            WHERE pm.pharmacy_id = ?
        ");
        if ($stats_stmt) {
            $stats_stmt->bind_param("i", $pharmacy['id']);
            $stats_stmt->execute();
            $stats_result = $stats_stmt->get_result();
            if ($row = $stats_result->fetch_assoc()) {
                $inventory_stats = $row;
            }
            $stats_stmt->close();
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
    <title>Inventory - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                            <a class="nav-link text-white" href="manage_medicines.php">
                                <i class="fas fa-pills me-2"></i>Manage Medicines
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="inventory.php">
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
                        <i class="fas fa-boxes text-primary"></i> Inventory Overview
                    </h1>
                    <a href="add_medicine.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i>Add Medicine
                    </a>
                </div>

                <!-- Inventory Stats -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3 class="mb-0"><?php echo $inventory_stats['total']; ?></h3>
                                        <p class="mb-0">Total Medicines</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-pills fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3 class="mb-0"><?php echo $inventory_stats['in_stock']; ?></h3>
                                        <p class="mb-0">In Stock</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3 class="mb-0"><?php echo $inventory_stats['low_stock']; ?></h3>
                                        <p class="mb-0">Low Stock</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3 class="mb-0"><?php echo $inventory_stats['out_of_stock']; ?></h3>
                                        <p class="mb-0">Out of Stock</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-times-circle fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Stock Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="inventoryChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alerts</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                // Get low stock medicines using the correct relationship
                                $low_stock_medicines = [];
                                if ($pharmacy) {
                                    $low_stmt = $conn->prepare("
                                        SELECT m.name, pm.stock as stock_quantity 
                                        FROM medicines m
                                        JOIN pharmacy_medicines pm ON m.id = pm.medicine_id
                                        WHERE pm.pharmacy_id = ? 
                                        AND pm.stock BETWEEN 1 AND 10
                                        ORDER BY pm.stock ASC 
                                        LIMIT 5
                                    ");
                                    if ($low_stmt) {
                                        $low_stmt->bind_param("i", $pharmacy['id']);
                                        $low_stmt->execute();
                                        $low_result = $low_stmt->get_result();
                                        while ($row = $low_result->fetch_assoc()) {
                                            $low_stock_medicines[] = $row;
                                        }
                                        $low_stmt->close();
                                    }
                                }
                                ?>
                                
                                <?php if (empty($low_stock_medicines)): ?>
                                    <div class="text-center text-muted">
                                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                                        <p>No low stock alerts</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($low_stock_medicines as $medicine): ?>
                                    <div class="alert alert-warning d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong><?php echo e($medicine['name']); ?></strong>
                                            <br>
                                            <small>Stock: <?php echo $medicine['stock_quantity']; ?></small>
                                        </div>
                                        <a href="manage_medicines.php" class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inventory Chart
        const ctx = document.getElementById('inventoryChart').getContext('2d');
        const inventoryChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['In Stock', 'Low Stock', 'Out of Stock'],
                datasets: [{
                    data: [
                        <?php echo $inventory_stats['in_stock']; ?>,
                        <?php echo $inventory_stats['low_stock']; ?>,
                        <?php echo $inventory_stats['out_of_stock']; ?>
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#ffc107',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>