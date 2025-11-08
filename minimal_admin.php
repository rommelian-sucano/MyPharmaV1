<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get pending pharmacies
$pending_pharmacies_result = $conn->query("SELECT * FROM pharmacies WHERE verified = 0 ORDER BY name");

// Get verified pharmacies
$verified_pharmacies_result = $conn->query("SELECT * FROM pharmacies WHERE verified = 1 ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minimal Admin - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-tachometer-alt"></i> Minimal Admin Dashboard
                    </h1>
                </div>

                <!-- Stats Section -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <h3 class="text-primary"><?php echo $verified_pharmacies_result->num_rows; ?></h3>
                                <p class="mb-0">Verified Pharmacies</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <h3 class="text-warning"><?php echo $pending_pharmacies_result->num_rows; ?></h3>
                                <p class="mb-0">Pending Pharmacies</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pharmacies Section -->
                <div class="row" id="pharmacies">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-clinic-medical"></i> Pharmacies Management
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Pending Pharmacies -->
                                <h6 class="mb-3">Pending Pharmacies</h6>
                                <?php if ($pending_pharmacies_result->num_rows === 0): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> No pending pharmacy registrations.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Address</th>
                                                    <th>Contact</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($pharmacy = $pending_pharmacies_result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($pharmacy['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($pharmacy['address']); ?></td>
                                                        <td><?php echo htmlspecialchars($pharmacy['contact']); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Verified Pharmacies -->
                                <h6 class="mt-4 mb-3">Verified Pharmacies</h6>
                                <?php if ($verified_pharmacies_result->num_rows === 0): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> No verified pharmacies.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Address</th>
                                                    <th>Contact</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($pharmacy = $verified_pharmacies_result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($pharmacy['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($pharmacy['address']); ?></td>
                                                        <td><?php echo htmlspecialchars($pharmacy['contact']); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>