NEW_FILE_CODE
<?php
require_once 'db.php';
require_once 'auth.php';
requireRole(['admin','staff','editor']);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=medicines_export.csv');

$out = fopen('php://output', 'w');
fputcsv($out, ['id','brand_name','scientific_name','manufacturer','dosage_form','strength','status']);

$sql = "SELECT id, brand_name, scientific_name, manufacturer, dosage_form, strength, status FROM medicines ORDER BY brand_name ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    fputcsv($out, $row);
}
$stmt->close();
fclose($out);