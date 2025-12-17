<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle approval/rejection actions
$message = '';
$error = '';

if (isset($_POST['approve_user'])) {
    $user_id = intval($_POST['user_id']);
    
    // First get user info to check if they have pharmacy info
    $getUserStmt = $conn->prepare("SELECT pharmacy_name, pharmacy_address, pharmacy_lat, pharmacy_lng, pharmacy_contact FROM users WHERE id = ? AND status = 'pending'");
    $getUserStmt->bind_param("i", $user_id);
    $getUserStmt->execute();
    $userResult = $getUserStmt->get_result();
    
    if ($userResult && $userResult->num_rows > 0) {
        $user = $userResult->fetch_assoc();
        
        // If user has pharmacy info, create pharmacy entry
        if (!empty($user['pharmacy_name']) && !empty($user['pharmacy_address']) && !empty($user['pharmacy_lat']) && !empty($user['pharmacy_lng']) && !empty($user['pharmacy_contact'])) {
            // Insert into pharmacies table
            $pharmacyStmt = $conn->prepare("INSERT INTO pharmacies (name, address, lat, lng, contact, verified) VALUES (?, ?, ?, ?, ?, 0)");
            $pharmacyStmt->bind_param("ssssss", $user['pharmacy_name'], $user['pharmacy_address'], $user['pharmacy_lat'], $user['pharmacy_lng'], $user['pharmacy_contact']);
            
            if ($pharmacyStmt->execute()) {
                // Update user status to approved and role to staff
                $stmt = $conn->prepare("UPDATE users SET status = 'approved', role = 'staff' WHERE id = ? AND status = 'pending'");
                $stmt->bind_param("i", $user_id);
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $message = "User and pharmacy approved successfully. The user is now a staff member and their pharmacy is pending verification.";
                    } else {
                        $error = "User not found or already processed.";
                    }
                } else {
                    $error = "Error approving user: " . $conn->error;
                }
                $stmt->close();
            } else {
                $error = "Error registering pharmacy: " . $conn->error;
            }
            $pharmacyStmt->close();
        } else {
            // Regular user approval
            $stmt = $conn->prepare("UPDATE users SET status = 'approved', role = 'staff' WHERE id = ? AND status = 'pending'");
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $message = "User approved successfully. The user is now a staff member.";
                } else {
                    $error = "User not found or already processed.";
                }
            } else {
                $error = "Error approving user: " . $conn->error;
            }
            $stmt->close();
        }
    } else {
        $error = "User not found or already processed.";
    }
    $getUserStmt->close();
}

if (isset($_POST['reject_user'])) {
    $user_id = intval($_POST['user_id']);
    
    $stmt = $conn->prepare("UPDATE users SET status = 'rejected' WHERE id = ? AND status = 'pending'");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $message = "User rejected successfully.";
        } else {
            $error = "User not found or already processed.";
        }
    } else {
        $error = "Error rejecting user: " . $conn->error;
    }
    $stmt->close();
}

// Get pending users with pharmacy info
$stmt = $conn->prepare("SELECT id, name, email, pharmacy_name, pharmacy_address, pharmacy_lat, pharmacy_lng, pharmacy_contact, created_at FROM users WHERE status = 'pending' ORDER BY created_at DESC");
$stmt->execute();
$pending_users_result = $stmt->get_result();
$pending_users = [];
while ($row = $pending_users_result->fetch_assoc()) {
    $pending_users[] = $row;
}
$stmt->close();

// Get count of pending users for badge
$pending_count_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'pending'");
$pending_count = 0;
if ($pending_count_result && $row = $pending_count_result->fetch_assoc()) {
    $pending_count = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Approvals - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center text-white mb-4">
                        <h4><i class="fas fa-user-shield"></i> Admin Panel</h4>
                        <p class="mb-0"><?php echo htmlspecialchars($_SESSION['name']); ?></p>
                        <small class="text-muted">Administrator</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="admin_dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="#">
                                <i class="fas fa-user-check me-2"></i>Approvals
                                <?php if ($pending_count > 0): ?>
                                    <span class="badge bg-danger float-end"><?php echo $pending_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="admin_invite.php">
                                <i class="fas fa-user-plus me-2"></i>Invite Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-user-check"></i> User Approvals
                    </h1>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Pending Users Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-clock"></i> Pending User Approvals
                                </h5>
                                <?php if ($pending_count > 0): ?>
                                    <span class="badge bg-light text-dark"><?php echo $pending_count; ?> pending</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <?php if (count($pending_users) === 0): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> No pending user registrations.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Pharmacy Info</th>
                                                    <th>Registration Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pending_users as $user): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                        <td>
                                                            <?php if (!empty($user['pharmacy_name'])): ?>
                                                                <strong><?php echo htmlspecialchars($user['pharmacy_name']); ?></strong><br>
                                                                <?php echo htmlspecialchars($user['pharmacy_address']); ?><br>
                                                                Contact: <?php echo htmlspecialchars($user['pharmacy_contact']); ?>
                                                                <?php if (!empty($user['pharmacy_lat']) && !empty($user['pharmacy_lng'])): ?>
                                                                    <button class="btn btn-sm btn-outline-primary mt-1" type="button" data-bs-toggle="collapse" data-bs-target="#pharmacyMap<?php echo $user['id']; ?>" aria-expanded="false" aria-controls="pharmacyMap<?php echo $user['id']; ?>">
                                                                        Show Location
                                                                    </button>
                                                                    <div class="collapse mt-2" id="pharmacyMap<?php echo $user['id']; ?>">
                                                                        <div class="map-container" style="height: 200px;" 
                                                                             data-lat="<?php echo $user['pharmacy_lat']; ?>" 
                                                                             data-lng="<?php echo $user['pharmacy_lng']; ?>"
                                                                             data-name="<?php echo htmlspecialchars($user['pharmacy_name']); ?>">
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="text-muted">Staff member (no pharmacy)</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo date('M j, Y g:i A', strtotime($user['created_at'])); ?></td>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                    <button type="submit" name="approve_user" class="btn btn-sm btn-success">
                                                                        <i class="fas fa-check me-1"></i>Approve
                                                                    </button>
                                                                </form>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                    <button type="submit" name="reject_user" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this user registration?')">
                                                                        <i class="fas fa-times me-1"></i>Reject
                                                                    </button>
                                                                </form>
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
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize maps for pharmacy locations
        document.addEventListener('DOMContentLoaded', function() {
            // Load Leaflet CSS and JS dynamically if not already loaded
            if (typeof L === 'undefined') {
                const leafletCSS = document.createElement('link');
                leafletCSS.rel = 'stylesheet';
                leafletCSS.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(leafletCSS);
                
                const leafletJS = document.createElement('script');
                leafletJS.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                leafletJS.onload = function() {
                    initializeMaps();
                };
                document.head.appendChild(leafletJS);
            } else {
                initializeMaps();
            }
            
            function initializeMaps() {
                // Find all map containers
                const mapContainers = document.querySelectorAll('.map-container');
                mapContainers.forEach(container => {
                    const lat = parseFloat(container.getAttribute('data-lat'));
                    const lng = parseFloat(container.getAttribute('data-lng'));
                    const name = container.getAttribute('data-name');
                    
                    if (!isNaN(lat) && !isNaN(lng)) {
                        // Create map
                        const map = L.map(container).setView([lat, lng], 15);
                        
                        // Add OpenStreetMap tiles
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                        }).addTo(map);
                        
                        // Add marker
                        const marker = L.marker([lat, lng]).addTo(map);
                        marker.bindPopup(`<b>${name}</b><br>Registered Location`);
                    }
                });
            }
        });
    </script>
</body>
</html><?php
// Simple diagnostic script
echo "<h1>MyPharma Diagnostic</h1>";
echo "<p>If you can see this, PHP is working.</p>";

// Try to include db.php
if (file_exists('db.php')) {
    echo "<p style='color: green;'>✓ db.php file exists</p>";
    
    // Try to include it
    try {
        include 'db.php';
        echo "<p style='color: green;'>✓ db.php included successfully</p>";
        
        // Test database connection
        if ($conn) {
            echo "<p style='color: green;'>✓ Database connection established</p>";
            
            // Test a simple query
            $result = $conn->query("SELECT 1 as test");
            if ($result) {
                echo "<p style='color: green;'>✓ Database query working</p>";
                
                // Check if users table exists
                $usersTable = $conn->query("SHOW TABLES LIKE 'users'");
                if ($usersTable && $usersTable->num_rows > 0) {
                    echo "<p style='color: green;'>✓ Users table exists</p>";
                    
                    // Check columns in users table
                    $columns = $conn->query("SHOW COLUMNS FROM users");
                    if ($columns) {
                        echo "<p style='color: green;'>✓ Can read users table structure</p>";
                        echo "<h3>Users table columns:</h3><ul>";
                        while ($column = $columns->fetch_assoc()) {
                            echo "<li>" . $column['Field'] . " (" . $column['Type'] . ")</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<p style='color: red;'>✗ Cannot read users table structure: " . $conn->error . "</p>";
                    }
                } else {
                    echo "<p style='color: red;'>✗ Users table does not exist</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Database query failed: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Database connection failed</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error including db.php: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ db.php file does not exist</p>";
    echo "<p>Current directory: " . getcwd() . "</p>";
}

// Check other important files
echo "<h3>File checks:</h3>";
$files = ['admin_approvals.php', 'login.php', 'register_invite.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $file exists</p>";
    } else {
        echo "<p style='color: red;'>✗ $file does not exist</p>";
    }
}

echo "<p><a href='admin_dashboard.php'>Back to Admin Dashboard</a></p>";
?><?php
session_start();
include 'db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

// Check if token is provided
if (!isset($_GET['token'])) {
    $error = "Invalid registration link.";
} else {
    $token = $_GET['token'];
    
    // Check if invitation is valid and not used
    $stmt = $conn->prepare("SELECT id, invite_type FROM invitations WHERE token = ? AND used = 0 AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $error = "Invalid or expired registration link.";
        $stmt->close();
    } else {
        $invitation = $result->fetch_assoc();
        $invite_type = $invitation['invite_type'];
        $invitation_id = $invitation['id'];
        $stmt->close();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Pharmacy fields (only for pharmacy owners)
            if ($invite_type === 'pharmacy_owner') {
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
                // For pharmacy owners, validate pharmacy fields
                elseif ($invite_type === 'pharmacy_owner' && (empty($pharmacy_name) || empty($pharmacy_address) || empty($pharmacy_lat) || empty($pharmacy_lng) || empty($pharmacy_contact))) {
                    $error = "Please fill in all pharmacy information.";
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
                        
                        // Insert user into database with 'pending' role and 'pending' status
                        if ($invite_type === 'pharmacy_owner') {
                            // Pharmacy owner registration
                            $insertStmt = $conn->prepare("INSERT INTO users (name, email, password, role, pharmacy_name, pharmacy_address, pharmacy_lat, pharmacy_lng, pharmacy_contact, status) VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, 'pending')");
                            $insertStmt->bind_param("ssssssdds", $name, $email, $hashed_password, $pharmacy_name, $pharmacy_address, $pharmacy_lat, $pharmacy_lng, $pharmacy_contact);
                        } else {
                            // Staff member registration
                            $insertStmt = $conn->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'pending', 'pending')");
                            $insertStmt->bind_param("sss", $name, $email, $hashed_password);
                        }
                        
                        if ($insertStmt->execute()) {
                            // Mark invitation as used
                            $updateStmt = $conn->prepare("UPDATE invitations SET used = 1, used_by = ?, used_at = NOW() WHERE id = ?");
                            $user_id = $conn->insert_id;
                            $updateStmt->bind_param("ii", $user_id, $invitation_id);
                            $updateStmt->execute();
                            $updateStmt->close();
                            
                            $success = "Your account has been submitted and is pending admin approval.";
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
    }
}
// ... rest of the existing HTML code remains the same ...