<?php
// c:\xampp\htdocs\MyPharmaV1\medicine_list.php
require_once 'auth.php';
require_once 'db.php';
requireRole(['admin', 'staff', 'editor', 'viewer']);

// Get user's pharmacy
$user_id = $_SESSION['user_id'];
$pharmacy = null;

// Try to get pharmacy from user_pharmacies table
$stmt = $conn->prepare("
    SELECT p.id, p.name, up.user_role 
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

// If not found, check old system
if (!$pharmacy) {
    $stmt = $conn->prepare("
        SELECT 
            pharmacy_name AS name
        FROM users 
        WHERE id = ? AND pharmacy_name IS NOT NULL AND pharmacy_name != ''
        LIMIT 1
    ");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $pharmacy_data = $result->fetch_assoc();
            // Try to find pharmacy ID by name
            $pharm_stmt = $conn->prepare("SELECT id FROM pharmacies WHERE name = ?");
            $pharm_stmt->bind_param("s", $pharmacy_data['name']);
            $pharm_stmt->execute();
            $pharm_result = $pharm_stmt->get_result();
            if ($pharm_result && $pharm_result->num_rows > 0) {
                $pharm_row = $pharm_result->fetch_assoc();
                $pharmacy = [
                    'id' => $pharm_row['id'],
                    'name' => $pharmacy_data['name']
                ];
            }
            $pharm_stmt->close();
        }
        $stmt->close();
    }
}

$csrf = getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Management - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Medicine Management<?php if ($pharmacy): ?> - <?php echo htmlspecialchars($pharmacy['name']); ?><?php endif; ?></h1>
        <div>
            <?php if (in_array($_SESSION['role'], ['admin','editor','staff'])): ?>
                <a href="medicine_form.php" class="btn btn-primary btn-sm">Add Medicine</a>
                <a href="medicine_import.php" class="btn btn-outline-primary btn-sm">Import CSV</a>
                <a href="medicine_export.php" class="btn btn-outline-secondary btn-sm">Export CSV</a>
            <?php endif; ?>
            <a href="dashboard_home.php" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    <?php if (!$pharmacy): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> No pharmacy assigned to your account. Please contact administrator.
        </div>
    <?php else: ?>

    <div class="card p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label">Brand Name</label>
                <input class="form-control" id="brandInput" />
            </div>
            <div class="col-md-4">
                <label class="form-label">Generic Name</label>
                <input class="form-control" id="genericInput" />
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-success w-100" onclick="loadMedicines()">Search</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0" id="medTable">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Brand Name</th>
                        <th>Generic Name</th>
                        <th>Manufacturer</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<script>
const csrf = "<?php echo $csrf; ?>";
const pharmacyId = <?php echo $pharmacy ? $pharmacy['id'] : 'null'; ?>;

async function loadMedicines() {
    if (!pharmacyId) return;
    
    const brand = document.getElementById('brandInput').value || '';
    const generic = document.getElementById('genericInput').value || '';
    
    // Add pharmacy ID to the request
    const url = `api/medicines.php?pharmacy_id=${pharmacyId}&brand=${encodeURIComponent(brand)}&generic=${encodeURIComponent(generic)}`;
    const res = await fetch(url);
    const data = await res.json();
    const tbody = document.querySelector('#medTable tbody');
    tbody.innerHTML = '';
    if (!Array.isArray(data) || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No medicines found.</td></tr>';
        return;
    }
    for (const m of data) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${m.id}</td>
            <td>${escapeHtml(m.brand_name || '')}</td>
            <td>${escapeHtml(m.scientific_name || '')}</td>
            <td>${escapeHtml(m.manufacturer || '')}</td>
            <td>₱${Number(m.price || 0).toFixed(2)}</td>
            <td>
                <span class="badge ${Number(m.stock) > 0 ? (Number(m.stock) < 10 ? 'bg-warning' : 'bg-success') : 'bg-secondary'}">
                    ${Number(m.stock) > 0 ? m.stock : 'Out of Stock'}
                </span>
            </td>
            <td class="text-end">
                <a href="medicine_form.php?id=${m.id}&pharmacy_id=${pharmacyId}" class="btn btn-sm btn-outline-primary me-2">Edit</a>
                <?php if (in_array($_SESSION['role'], ['admin'])): ?>
                <button class="btn btn-sm btn-outline-danger" onclick="delMed(${m.id})">Delete</button>
                <?php endif; ?>
            </td>
        `;
        tbody.appendChild(tr);
    }
}
function escapeHtml(str) {
    return (str || '').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
}
async function delMed(id) {
    if (!confirm('Delete this medicine?')) return;
    const res = await fetch(`api/medicines.php?id=${id}`, { method: 'DELETE', headers: { 'X-CSRF-Token': csrf } });
    if (res.ok) loadMedicines();
}
if (pharmacyId) {
    loadMedicines();
}
</script>
</body>
</html>