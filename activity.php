NEW_FILE_CODE
<?php
require_once '../db.php';
require_once '../auth.php';
requireRole(['admin','staff','editor','viewer']);
header('Content-Type: application/json');

$limit = isset($_GET['limit']) ? max(1, min(50, (int)$_GET['limit'])) : 10;

$stmt = $conn->prepare("
  SELECT l.id, l.action, l.entity_type, l.entity_id, l.details, l.created_at, u.name AS user_name
  FROM activity_logs l
  LEFT JOIN users u ON u.id = l.user_id
  ORDER BY l.created_at DESC
  LIMIT ?
");
$stmt->bind_param("i", $limit);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($row = $res->fetch_assoc()) { $out[] = $row; }
$stmt->close();

echo json_encode($out);