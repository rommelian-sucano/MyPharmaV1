NEW_FILE_CODE
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
</html>