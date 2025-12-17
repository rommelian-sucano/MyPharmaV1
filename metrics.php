NEW_FILE_CODE
<?php
require_once 'db.php';
require_once 'auth.php';
requireRole(['admin', 'staff', 'editor', 'viewer']);
header('Content-Type: application/json');

function countQuery(mysqli $conn, string $sql): int {
    $c = 0;
    if ($res = $conn->query($sql)) {
        $row = $res->fetch_assoc();
        $c = (int)($row['c'] ?? 0);
    }
    return $c;
}

$totalMedicines = countQuery($conn, "SELECT COUNT(*) AS c FROM medicines");
$pendingApprovals = 0;
if ($conn->query("SELECT 1 FROM information_schema.COLUMNS WHERE table_schema=DATABASE() AND table_name='users' AND column_name='status'")->num_rows) {
    $pendingApprovals = countQuery($conn, "SELECT COUNT(*) AS c FROM users WHERE status='pending'");
}
$totalUsers = countQuery($conn, "SELECT COUNT(*) AS c FROM users");
$totalPharmacies = countQuery($conn, "SELECT COUNT(*) AS c FROM pharmacies WHERE verified=1");

// Thresholds from system_settings (fallbacks if missing)
$lowStockThreshold = 10;
$expiryDays = 30;

$chk = $conn->query("SELECT 1 FROM information_schema.TABLES WHERE table_schema = DATABASE() AND table_name = 'system_settings' LIMIT 1");
if ($chk && $chk->num_rows > 0) {
    if ($stmt = $conn->prepare("SELECT `value` FROM system_settings WHERE `key`='low_stock_threshold'")) {
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) { $lowStockThreshold = (int)$row['value']; }
        $stmt->close();
    }
    if ($stmt = $conn->prepare("SELECT `value` FROM system_settings WHERE `key`='expiry_alert_days'")) {
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) { $expiryDays = (int)$row['value']; }
        $stmt->close();
    }
}

$lowStockCount = 0;
if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM pharmacy_medicines WHERE stock < ?")) {
    $stmt->bind_param("i", $lowStockThreshold);
    $stmt->execute();
    $res = $stmt->get_result();
    $lowStockCount = (int)($res->fetch_assoc()['c'] ?? 0);
    $stmt->close();
}

$expiringSoonCount = 0;
if ($conn->query("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name='pharmacy_medicines' AND column_name='expiry_date'")->num_rows) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM pharmacy_medicines WHERE expiry_date IS NOT NULL AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)");
    $stmt->bind_param("i", $expiryDays);
    $stmt->execute();
    $res = $stmt->get_result();
    $expiringSoonCount = (int)($res->fetch_assoc()['c'] ?? 0);
    $stmt->close();
}

// Searches last 7 days (mock or from search_logs if present)
$searches7Days = [];
if ($conn->query("SELECT 1 FROM information_schema.TABLES WHERE table_schema=DATABASE() AND table_name='search_logs'")->num_rows) {
    $stmt = $conn->prepare("
        SELECT DATE_FORMAT(created_at, '%a') AS day, COUNT(*) AS count
        FROM search_logs
        WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 DAY)
        GROUP BY DATE(created_at)
        ORDER BY DATE(created_at)
    ");
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $searches7Days[] = ['day' => $row['day'], 'count' => (int)$row['count']];
    }
    $stmt->close();
}
if (empty($searches7Days)) {
    $searches7Days = [
        ['day' => 'Mon', 'count' => 42],
        ['day' => 'Tue', 'count' => 38],
        ['day' => 'Wed', 'count' => 57],
        ['day' => 'Thu', 'count' => 35],
        ['day' => 'Fri', 'count' => 64],
        ['day' => 'Sat', 'count' => 72],
        ['day' => 'Sun', 'count' => 50],
    ];
}

echo json_encode([
    'totalMedicines' => $totalMedicines,
    'pendingApprovals' => $pendingApprovals,
    'totalUsers' => $totalUsers,
    'totalPharmacies' => $totalPharmacies,
    'lowStock' => $lowStockCount,
    'expiringSoon' => $expiringSoonCount,
    'searches7Days' => $searches7Days,
]);