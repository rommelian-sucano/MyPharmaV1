<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
requireRole(['admin', 'staff', 'editor', 'viewer']);

$brand = trim($_GET['brand'] ?? '');
$generic = trim($_GET['generic'] ?? '');
$manufacturer = trim($_GET['manufacturer'] ?? '');
$message = $_GET['message'] ?? '';

$csrf = getCsrfToken();

// Handle delete (admin/editor only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    requireRole(['admin', 'editor']);
    $deleteId = (int)($_POST['delete_id'] ?? 0);
    $token = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($token)) {
        http_response_code(400);
        $message = 'Invalid CSRF token.';
    } elseif ($deleteId <= 0) {
        http_response_code(400);
        $message = 'Invalid medicine id.';
    } else {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("DELETE FROM medicines WHERE id=? LIMIT 1");
            $stmt->bind_param("i", $deleteId);
            $stmt->execute();
            $deleted = $stmt->affected_rows === 1;
            $stmt->close();

            if ($deleted) {
                $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details) VALUES (?, 'delete', 'medicine', ?, 'Medicine deleted')");
                $userId = (int)($_SESSION['user_id'] ?? 0);
                $stmt->bind_param("ii", $userId, $deleteId);
                $stmt->execute();
                $stmt->close();

                $conn->commit();
                header("Location: medicines.php?message=Medicine deleted successfully");
                exit();
            } else {
                $conn->rollback();
                $message = 'Not found or no change.';
            }
        } catch (Throwable $e) {
            $conn->rollback();
            $message = 'Delete failed.';
        }
    }
}

// Build server-side search query
function columnExists(mysqli $conn, string $table, string $column): bool {
    $sql = "SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $table, $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $ok = $res && $res->num_rows > 0;
    $stmt->close();
    return $ok;
}

$hasBrand = columnExists($conn, 'medicines', 'brand_name');
$hasGeneric = columnExists($conn, 'medicines', 'scientific_name');

$conditions = [];
$params = [];
$types = '';

if ($brand !== '' && $hasBrand) { $conditions[] = "brand_name LIKE ?"; $params[] = "%{$brand}%"; $types .= "s"; }
if ($generic !== '' && $hasGeneric) { $conditions[] = "scientific_name LIKE ?"; $params[] = "%{$generic}%"; $types .= "s"; }
if ($manufacturer !== '') { $conditions[] = "manufacturer LIKE ?"; $params[] = "%{$manufacturer}%"; $types .= "s"; }

$selectCols = "id, " .
    ($hasBrand ? "brand_name" : "'' AS brand_name") . ", " .
    ($hasGeneric ? "scientific_name" : "'' AS scientific_name") . ", " .
    "manufacturer, dosage_form, strength, status";

$sql = "SELECT {$selectCols} FROM medicines";
if ($conditions) { $sql .= " WHERE " . implode(" AND ", $conditions); }
$sql .= " ORDER BY " . ($hasBrand ? "brand_name" : ($hasGeneric ? "scientific_name" : "id")) . " ASC";

$rows = [];
$stmt = $conn->prepare($sql);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) { $rows[] = $row; }
$stmt->close();

function eout($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicines - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table thead th { white-space: nowrap; }
        .filter-row .form-control { min-width: 160px; }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Medicine Management</h1>
        <div>
            <?php if (in_array($_SESSION['role'], ['admin','editor','staff'])): ?>
                <a href="medicine_form.php" class="btn btn-primary btn-sm">Add Medicine</a>
            <?php endif; ?>
            <a href="dashboard_home.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo eout($message); ?></div>
    <?php endif; ?>

    <form method="get" class="card card-body mb-3 filter-row">
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label">Brand Name</label>
                <input type="text" name="brand" class="form-control" value="<?php echo eout($brand); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Generic Name</label>
                <input type="text" name="generic" class="form-control" value="<?php echo eout($generic); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Manufacturer</label>
                <input type="text" name="manufacturer" class="form-control" value="<?php echo eout($manufacturer); ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-success w-100" type="submit">Search</button>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Brand Name</th>
                        <th>Generic Name</th>
                        <th>Manufacturer</th>
                        <th>Dosage Form</th>
                        <th>Strength</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="8" class="text-center text-muted">No medicines found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?php echo (int)$r['id']; ?></td>
                                <td><?php echo eout($r['brand_name']); ?></td>
                                <td><?php echo eout($r['scientific_name']); ?></td>
                                <td><?php echo eout($r['manufacturer']); ?></td>
                                <td><?php echo eout($r['dosage_form']); ?></td>
                                <td><?php echo eout($r['strength']); ?></td>
                                <td>
                                    <span class="badge 
                                        <?php echo $r['status']==='active'?'bg-success':($r['status']==='pending'?'bg-warning text-dark':'bg-secondary'); ?>">
                                        <?php echo eout($r['status']); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="medicine_form.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <?php if (in_array($_SESSION['role'], ['admin','editor'])): ?>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this medicine?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo eout($csrf); ?>">
        <input type="hidden" name="delete_id" value="<?php echo (int)$r['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>