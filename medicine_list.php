
<?php
// c:\xampp\htdocs\MyPharmaV1\medicine_list.php
require_once 'auth.php';
requireRole(['admin', 'staff', 'editor', 'viewer']);
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
        <h1 class="h4 mb-0">Medicine Management</h1>
        <div>
            <?php if (in_array($_SESSION['role'], ['admin','editor','staff'])): ?>
                <a href="medicine_form.php" class="btn btn-primary btn-sm">Add Medicine</a>
                <a href="medicine_import.php" class="btn btn-outline-primary btn-sm">Import CSV</a>
                <a href="medicine_export.php" class="btn btn-outline-secondary btn-sm">Export CSV</a>
            <?php endif; ?>
            <a href="dashboard_home.php" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

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
                        <th>Stock Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<script>
const csrf = "<?php echo $csrf; ?>";

async function loadMedicines() {
    const brand = document.getElementById('brandInput').value || '';
    const generic = document.getElementById('genericInput').value || '';
    const res = await fetch(`api/medicines.php?brand=${encodeURIComponent(brand)}&generic=${encodeURIComponent(generic)}`);
    const data = await res.json();
    const tbody = document.querySelector('#medTable tbody');
    tbody.innerHTML = '';
    if (!Array.isArray(data) || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No medicines found.</td></tr>';
        return;
    }
    for (const m of data) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${m.id}</td>
            <td>${escapeHtml(m.brand_name || '')}</td>
            <td>${escapeHtml(m.scientific_name || '')}</td>
            <td>${escapeHtml(m.manufacturer || '')}</td>
            <td>
                <span class="badge ${Number(m.stock) > 0 ? 'bg-success' : 'bg-secondary'}">
                    ${Number(m.stock) > 0 ? 'In Stock' : 'Out of Stock'}
                </span>
            </td>
            <td class="text-end">
                <a href="medicine_form.php?id=${m.id}" class="btn btn-sm btn-outline-primary me-2">Edit</a>
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
loadMedicines();
</script>
</body>
</html>