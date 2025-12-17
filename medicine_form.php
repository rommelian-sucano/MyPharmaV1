<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
requireRole(['admin', 'staff', 'editor']); // viewers cannot edit

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';

$dosageForms = ['Tablet','Capsule','Syrup','Injection','Ointment','Cream','Drops','Inhaler','Gel','Patch'];
$allowedStatus = ['active','inactive','pending'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = $id > 0;

$errors = [];
$success = '';
$csrf = getCsrfToken();

$fields = [
    'brand_name' => '',
    'scientific_name' => '',
    'manufacturer' => '',
    'dosage_form' => '',
    'strength' => '',
    'description' => '',
    'uses' => '',
    'side_effects' => '',
    'contraindications' => '',
    'status' => 'active',
    'image_path' => ''
];

// Load existing data for edit
if ($editing) {
    $stmt = $conn->prepare("SELECT * FROM medicines WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1) {
        $fields = array_merge($fields, $res->fetch_assoc());
    } else {
        $errors[] = "Medicine not found.";
    }
    $stmt->close();
}

// Handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $errors[] = "Invalid CSRF token.";
    } else {
        // Collect and validate inputs
        $fields['brand_name'] = trim($_POST['brand_name'] ?? '');
        $fields['scientific_name'] = trim($_POST['scientific_name'] ?? '');
        $fields['manufacturer'] = trim($_POST['manufacturer'] ?? '');
        $fields['dosage_form'] = trim($_POST['dosage_form'] ?? '');
        $fields['strength'] = trim($_POST['strength'] ?? '');
        $fields['description'] = trim($_POST['description'] ?? '');
        $fields['uses'] = trim($_POST['uses'] ?? '');
        $fields['side_effects'] = trim($_POST['side_effects'] ?? '');
        $fields['contraindications'] = trim($_POST['contraindications'] ?? '');
        $fields['status'] = trim($_POST['status'] ?? 'active');

        if ($fields['brand_name'] === '') { $errors[] = "Brand Name is required."; }
        if ($fields['scientific_name'] === '') { $errors[] = "Generic Name is required."; }
        if ($fields['manufacturer'] === '') { $errors[] = "Manufacturer is required."; }
        if (!in_array($fields['dosage_form'], $dosageForms, true)) { $errors[] = "Invalid Dosage Form."; }
        if ($fields['strength'] === '') { $errors[] = "Strength is required."; }
        if (!in_array($fields['status'], $allowedStatus, true)) { $errors[] = "Invalid Status."; }

        // Optional image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['image']['tmp_name'];
            $name = $_FILES['image']['name'];
            $size = $_FILES['image']['size'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmp);
            finfo_close($finfo);

            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
            if (!isset($allowed[$mime])) {
                $errors[] = "Image must be JPG or PNG.";
            } elseif ($size > 2 * 1024 * 1024) {
                $errors[] = "Image must be <= 2MB.";
            } else {
                $ext = $allowed[$mime];
                $dir = __DIR__ . '/images/medicines';
                if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
                $safeBase = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($name, PATHINFO_FILENAME));
                $filename = $safeBase . '-' . time() . '.' . $ext;
                $destAbs = $dir . '/' . $filename;
                $destRel = 'images/medicines/' . $filename;
                if (move_uploaded_file($tmp, $destAbs)) {
                    $fields['image_path'] = $destRel;
                } else {
                    $errors[] = "Failed to save uploaded image.";
                }
            }
        }

        if (!$errors) {
            if ($editing) {
                $stmt = $conn->prepare("
                    UPDATE medicines
                       SET brand_name=?, scientific_name=?, manufacturer=?, dosage_form=?, strength=?,
                           description=?, uses=?, side_effects=?, contraindications=?, status=?, image_path=?, updated_at=NOW()
                     WHERE id=?
                ");
                $stmt->bind_param(
                    "sssssssssssi",
                    $fields['brand_name'],
                    $fields['scientific_name'],
                    $fields['manufacturer'],
                    $fields['dosage_form'],
                    $fields['strength'],
                    $fields['description'],
                    $fields['uses'],
                    $fields['side_effects'],
                    $fields['contraindications'],
                    $fields['status'],
                    $fields['image_path'],
                    $id
                );
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO medicines
                        (brand_name, scientific_name, manufacturer, dosage_form, strength,
                         description, uses, side_effects, contraindications, status, image_path, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->bind_param(
                    "sssssssssss",
                    $fields['brand_name'],
                    $fields['scientific_name'],
                    $fields['manufacturer'],
                    $fields['dosage_form'],
                    $fields['strength'],
                    $fields['description'],
                    $fields['uses'],
                    $fields['side_effects'],
                    $fields['contraindications'],
                    $fields['status'],
                    $fields['image_path']
                );
            }

            if ($stmt->execute()) {
                $stmt->close();

                // Log action
                $entityId = $editing ? $id : $conn->insert_id;
                $action = $editing ? 'update' : 'create';
                $details = $editing ? 'Medicine updated' : 'Medicine created';
                $userId = (int)($_SESSION['user_id'] ?? 0);

                $logStmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details) VALUES (?, ?, 'medicine', ?, ?)");
                $logStmt->bind_param("isis", $userId, $action, $entityId, $details);
                $logStmt->execute();
                $logStmt->close();

                header("Location: " . $base . "medicine_list.php?message=Saved successfully");
                exit();
            } else {
                $errors[] = "Database operation failed.";
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
    <title><?php echo $editing ? 'Edit' : 'Add'; ?> Medicine - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .img-preview { max-height: 120px; object-fit: contain; }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0"><?php echo $editing ? 'Edit' : 'Add'; ?> Medicine</h1>
        <div>
            <a href="<?php echo htmlspecialchars($base . 'medicine_list.php', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo e($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="card card-body" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Brand Name</label>
                <input type="text" name="brand_name" class="form-control" value="<?php echo e($fields['brand_name']); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Generic Name</label>
                <input type="text" name="scientific_name" class="form-control" value="<?php echo e($fields['scientific_name']); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Manufacturer</label>
                <input type="text" name="manufacturer" class="form-control" value="<?php echo e($fields['manufacturer']); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Dosage Form</label>
                <select name="dosage_form" class="form-select" required>
                    <option value="">Select...</option>
                    <?php foreach ($dosageForms as $df): ?>
                        <option value="<?php echo e($df); ?>" <?php if ($fields['dosage_form']===$df) echo 'selected'; ?>>
                            <?php echo e($df); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Strength</label>
                <input type="text" name="strength" class="form-control" value="<?php echo e($fields['strength']); ?>" required>
            </div>

            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2"><?php echo e($fields['description']); ?></textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Uses</label>
                <textarea name="uses" class="form-control" rows="2"><?php echo e($fields['uses']); ?></textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Side Effects</label>
                <textarea name="side_effects" class="form-control" rows="2"><?php echo e($fields['side_effects']); ?></textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Contraindications</label>
                <textarea name="contraindications" class="form-control" rows="2"><?php echo e($fields['contraindications']); ?></textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <?php foreach ($allowedStatus as $st): ?>
                        <option value="<?php echo e($st); ?>" <?php if ($fields['status']===$st) echo 'selected'; ?>>
                            <?php echo e(ucfirst($st)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Image (optional)</label>
                <input type="file" name="image" class="form-control" accept="image/jpeg,image/png">
                <?php if (!empty($fields['image_path'])): ?>
                    <div class="mt-2">
                        <img src="<?php echo e($fields['image_path']); ?>" alt="Medicine image" class="img-thumbnail img-preview">
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-3">
            <button class="btn btn-primary" type="submit"><?php echo $editing ? 'Update' : 'Save'; ?></button>
        </div>
    </form>
</div>
</body>
</html>