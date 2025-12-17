<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Generate invitation
if (isset($_POST['generate_invite'])) {
    $invite_type = $_POST['invite_type'];
    $token = bin2hex(random_bytes(16)); // Generate a random token
    
    // Insert invitation into database
    $stmt = $conn->prepare("INSERT INTO invitations (token, invite_type, created_by, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("ssi", $token, $invite_type, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $full_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/register_invite.php?token=" . $token;
        $message = "Invitation generated successfully!";
    } else {
        $error = "Error generating invitation: " . $conn->error;
    }
    $stmt->close();
}

// Delete invitation
if (isset($_POST['delete_invite'])) {
    $invite_id = $_POST['invite_id'];
    $stmt = $conn->prepare("DELETE FROM invitations WHERE id = ? AND used = 0");
    $stmt->bind_param("i", $invite_id);
    
    if ($stmt->execute()) {
        $message = "Invitation deleted successfully!";
    } else {
        $error = "Error deleting invitation: " . $conn->error;
    }
    $stmt->close();
}

// Get existing invitations with error handling
$invitations = [];
try {
    $stmt = $conn->prepare("SELECT i.*, u.name as created_by_name FROM invitations i LEFT JOIN users u ON i.created_by = u.id WHERE i.used = 0 ORDER BY i.created_at DESC");
    if ($stmt) {
        $stmt->execute();
        $invitations_result = $stmt->get_result();
        while ($row = $invitations_result->fetch_assoc()) {
            $invitations[] = $row;
        }
        $stmt->close();
    } else {
        $error = "Error preparing statement: " . $conn->error;
    }
} catch (Exception $e) {
    $error = "Error retrieving invitations: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Invitation System - MyPharma</title>
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
                        <i class="fas fa-user-plus"></i> Invitation System
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

                <!-- Generate Invitation Form -->
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-plus-circle"></i> Generate New Invitation
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="invite_type" class="form-label">Invitation Type</label>
                                        <select class="form-select" id="invite_type" name="invite_type" required>
                                            <option value="staff">Staff Member</option>
                                            <option value="pharmacy_owner">Pharmacy Owner</option>
                                        </select>
                                    </div>
                                    
                                    <button type="submit" name="generate_invite" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Generate Invitation
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Invitations -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-list"></i> Active Invitations
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($invitations) === 0): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> No active invitations.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Token</th>
                                                    <th>Type</th>
                                                    <th>Created By</th>
                                                    <th>Created At</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($invitations as $invite): 
                                                    $full_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/register_invite.php?token=" . $invite['token'];
                                                    $created_by_name = !empty($invite['created_by_name']) ? $invite['created_by_name'] : 'Unknown User';
                                                ?>
                                                    <tr>
                                                        <td><?php echo substr($invite['token'], 0, 8) . '...'; ?></td>
                                                        <td>
                                                            <?php if ($invite['invite_type'] === 'staff'): ?>
                                                                <span class="badge bg-primary">Staff</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-success">Pharmacy Owner</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($created_by_name); ?></td>
                                                        <td><?php echo date('M j, Y g:i A', strtotime($invite['created_at'])); ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-primary me-1" onclick="copyToClipboard('<?php echo $full_link; ?>')">
                                                                <i class="fas fa-copy"></i> Copy Link
                                                            </button>
                                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this invitation?');">
                                                                <input type="hidden" name="invite_id" value="<?php echo $invite['id']; ?>">
                                                                <button type="submit" name="delete_invite" class="btn btn-sm btn-outline-danger">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            </form>
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
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Link copied to clipboard!');
            }, function(err) {
                console.error('Could not copy text: ', err);
                // Fallback for older browsers
                var textArea = document.createElement("textarea");
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Link copied to clipboard!');
            });
        }
    </script>
</body>
</html>