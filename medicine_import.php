
<?php
require_once 'db.php';
require_once 'auth.php';
requireRole(['admin','editor']); // restrict import to admin/editor

$csrf = getCsrfToken();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $errors[] = 'Invalid CSRF token.';
    } elseif (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Upload a CSV file.';
    } else {
        $fh = fopen($_FILES['csv']['tmp_name'], 'r');
        if (!$fh) { $errors[] = 'Cannot read file.'; }
        else {
            $conn->begin_transaction();
            try {
                $header = fgetcsv($fh);
                // Expected columns: brand_name,scientific_name,manufacturer,dosage_form,strength,status
                $count = 0;
                while (($row = fgetcsv($fh)) !== false) {
                    $data = array_combine($header, $row);
                    $brand = trim($data['brand_name'] ?? '');
                    $generic = trim($data['scientific_name'] ?? '');
                    $manu = trim($data['manufacturer'] ?? '');
                    $dosage = trim($data['dosage_form'] ?? 'Tablet');
                    $strength = trim($data['strength'] ?? '');
                    $status = trim($data['status'] ?? 'active');
                    if ($brand === '' || $generic === '') { continue; }
                    $stmt = $conn->prepare("
                        INSERT INTO medicines (brand_name, scientific_name, manufacturer, dosage_form, strength, status, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    $stmt->bind_param("ssssss", $brand, $generic, $manu, $dosage, $strength, $status);
                    $stmt->execute();
                    $stmt->close();
                    $count++;
                }
                fclose($fh);
                $conn->commit();
                $success = "Imported {$count} records.";
            } catch (Throwable $e) {
                $conn->rollback();
                $errors[] = 'Import failed.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Import Medicines CSV</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h1 class="h4 mb-3">Import Medicines (CSV)</h1>
  <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
  <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul></div><?php endif; ?>
  <div class="card card-body">
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
      <div class="mb-3">
        <label class="form-label">CSV File</label>
        <input type="file" name="csv" accept=".csv" class="form-control" required>
        <small class="text-muted">Columns: brand_name, scientific_name, manufacturer, dosage_form, strength, status</small>
      </div>
      <button class="btn btn-primary" type="submit">Import</button>
      <a href="medicine_list.php" class="btn btn-outline-secondary">Back</a>
    </form>
  </div>
</div>
</body>
</html>// ... existing code ...
    <div class="row g-3 mb-4" id="metricCards">
        <div class="col-md-3">
            <div class="card p-3">
                <div class="h2 mb-0" id="totalMedicines">0</div>
                <div class="text-muted">Total Medicines</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="h2 mb-0" id="pendingApprovals">0</div>
                <div class="text-muted">Pending Approvals</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="h2 mb-0" id="totalUsers">0</div>
                <div class="text-muted">Total Users</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="h2 mb-0" id="totalPharmacies">0</div>
                <div class="text-muted">Verified Pharmacies</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card p-3">
                <div class="h2 mb-0 text-warning" id="lowStock">0</div>
                <div class="text-muted">Low Stock Items</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3">
                <div class="h2 mb-0 text-danger" id="expiringSoon">0</div>
                <div class="text-muted">Expiring Soon</div>
            </div>
        </div>
    </div>
    // ... existing code ...
    <div class="card p-3">
        <h2 class="h6 mb-3">Recent Activity</h2>
        <div id="recentActivity"></div>
    </div>
// ... existing code ...
<script>
async function loadMetrics() {
    const res = await fetch('api/metrics.php');
    const data = await res.json();
    document.getElementById('totalMedicines').textContent = data.totalMedicines ?? 0;
    document.getElementById('pendingApprovals').textContent = data.pendingApprovals ?? 0;
    document.getElementById('totalUsers').textContent = data.totalUsers ?? 0;
    document.getElementById('totalPharmacies').textContent = data.totalPharmacies ?? 0;
    document.getElementById('lowStock').textContent = data.lowStock ?? 0;
    document.getElementById('expiringSoon').textContent = data.expiringSoon ?? 0;
    // ... existing chart code ...
}
async function loadActivity() {
    const res = await fetch('api/activity.php?limit=10');
    const logs = await res.json();
    const container = document.getElementById('recentActivity');
    if (!Array.isArray(logs) || logs.length === 0) {
        container.innerHTML = '<p class="text-muted mb-0">No recent activity.</p>';
        return;
    }
    container.innerHTML = logs.map(l => `
        <div class="d-flex justify-content-between border-bottom py-2">
            <div>
                <strong>${escapeHtml(l.action || '')}</strong>
                <span class="text-muted">
                    on ${escapeHtml(l.entity_type || '')} #${Number(l.entity_id || 0)} by ${escapeHtml(l.user_name || 'System')}
                </span>
                ${l.details ? `<div class="text-muted small mt-1">${escapeHtml(l.details)}</div>` : ''}
            </div>
            <small class="text-muted">${escapeHtml((l.created_at || '').replace('T',' ').slice(0,16))}</small>
        </div>
    `).join('');
}
function escapeHtml(str){return (str||'').replace(/[&<>"']/g,s=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));}
loadMetrics();
loadActivity();
</script>