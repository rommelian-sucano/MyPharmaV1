CREATE TABLE IF NOT EXISTS activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  action VARCHAR(50) NOT NULL,
  entity_type VARCHAR(50) NOT NULL,
  entity_id INT NOT NULL,
  details TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_logs_entity (entity_type, entity_id),
  CONSTRAINT fk_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
<?php
// c:\xampp\htdocs\MyPharmaV1\debug_suite.php
// Comprehensive diagnostics: environment, DB, schema columns, file existence, and sample queries.

header('Content-Type: text/html; charset=utf-8');

function statusBadge($ok) {
    return $ok ? '<span style="color:#0a0">OK</span>' : '<span style="color:#c00">FAIL</span>';
}

$phpVersion = PHP_VERSION;
$root = __DIR__;
$issues = [];

echo "<h2>MyPharmaV1 Diagnostic Suite</h2>";
echo "<p>PHP: {$phpVersion}</p>";
echo "<hr>";

// DB connection
echo "<h3>Database Connection</h3>";
$dbOk = false;
$dbName = '';
if (file_exists($root . '/db.php')) {
    include $root . '/db.php'; // expects $conn and $dbname
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        $dbOk = true;
        $dbName = $dbname ?? '(unknown)';
        echo "<p>Connection: " . statusBadge(true) . " to database '{$dbName}'</p>";
    } else {
        $issues[] = "Database connection failed.";
        echo "<p>Connection: " . statusBadge(false) . "</p>";
    }
} else {
    $issues[] = "db.php not found.";
    echo "<p>db.php: " . statusBadge(false) . "</p>";
}

// Schema checks
$requiredTables = [
    'users',
    'pharmacies',
    'medicines',
    'pharmacy_medicines',
    'price_history',
    'notifications',
    'activity_logs' // required for dashboard logs
];
$tablesOk = true;
if ($dbOk) {
    echo "<h3>Tables</h3><ul>";
    foreach ($requiredTables as $t) {
        $exists = false;
        $stmt = $conn->prepare("SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = DATABASE() AND TABLE_NAME = ?");
        $stmt->bind_param("s", $t);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res && $res->num_rows > 0;
        $stmt->close();
        if (!$exists) { $tablesOk = false; $issues[] = "Missing table: {$t}"; }
        echo "<li>{$t}: " . statusBadge($exists) . "</li>";
    }
    echo "</ul>";
}

// Column checks
function columnExists(mysqli $conn, string $table, string $column): bool {
    $sql = "SELECT 1 FROM information_schema.columns 
            WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? 
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $table, $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $ok = $res && $res->num_rows > 0;
    $stmt->close();
    return $ok;
}
$columnsToCheck = [
    ['users','status'],
    ['pharmacies','name'], // must be 'name'
    ['medicines','brand_name'],
    ['medicines','scientific_name'],
    ['medicines','manufacturer'],      // extended field
    ['medicines','dosage_form'],       // extended field
    ['medicines','strength'],          // extended field
    ['activity_logs','user_id'],
    ['activity_logs','entity_type'],
    ['activity_logs','entity_id'],
];
if ($dbOk) {
    echo "<h3>Key Columns</h3><ul>";
    foreach ($columnsToCheck as [$tbl,$col]) {
        $ok = columnExists($conn, $tbl, $col);
        if (!$ok) { $issues[] = "Missing column: {$tbl}.{$col}"; }
        echo "<li>{$tbl}.{$col}: " . statusBadge($ok) . "</li>";
    }
    echo "</ul>";
}

// Sample queries
if ($dbOk) {
    echo "<h3>Sample Queries</h3><ul>";
    // Pending users count
    $pendingUsers = 0;
    if (columnExists($conn, 'users', 'status')) {
        if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM users WHERE status='pending'")) {
            $stmt->execute();
            $res = $stmt->get_result();
            $pendingUsers = (int)($res->fetch_assoc()['c'] ?? 0);
            $stmt->close();
        }
        echo "<li>Pending users: {$pendingUsers}</li>";
    } else {
        echo "<li>Pending users: status column missing</li>";
    }

    // Medicines count
    $medCount = 0;
    if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM medicines")) {
        $stmt->execute();
        $res = $stmt->get_result();
        $medCount = (int)($res->fetch_assoc()['c'] ?? 0);
        $stmt->close();
    }
    echo "<li>Total medicines: {$medCount}</li>";

    // Verified pharmacies
    $verifiedPharm = 0;
    if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM pharmacies WHERE verified=1")) {
        $stmt->execute();
        $res = $stmt->get_result();
        $verifiedPharm = (int)($res->fetch_assoc()['c'] ?? 0);
        $stmt->close();
    }
    echo "<li>Verified pharmacies: {$verifiedPharm}</li>";
    echo "</ul>";
}

// File existence and zero-size checks
echo "<h3>Critical Files</h3>";
$criticalFiles = [
    'index.php',
    'login.php',
    'logout.php',
    'staff_dashboard.php',
    'admin_dashboard.php',
    'medicine_list.php',
    'medicine_form.php',
    'get_notifications.php',
    'get_admin_updates.php',
    'api/metrics.php',
    'api/medicines.php',
    'db.php',
    'auth.php',
];
echo "<table border='1' cellspacing='0' cellpadding='6'><tr><th>File</th><th>Exists</th><th>Size (bytes)</th></tr>";
foreach ($criticalFiles as $f) {
    $path = $root . '/' . $f;
    $exists = file_exists($path);
    $size = $exists ? filesize($path) : 0;
    if (!$exists) { $issues[] = "Missing file: {$f}"; }
    echo "<tr><td>{$f}</td><td>" . ($exists ? 'Yes' : 'No') . "</td><td>{$size}</td></tr>";
}
echo "</table>";

// Summary
echo "<hr><h3>Summary</h3>";
if (empty($issues)) {
    echo "<p>All checks passed.</p>";
} else {
    echo "<ul>";
    foreach ($issues as $i) echo "<li style='color:#c00'>{$i}</li>";
    echo "</ul>";
    echo "<p>Fix the items above, then reload this page.</p>";
}

echo "<hr><p><small>Tip: If you see 404 Not Found, ensure the URL matches the folder name exactly and that the file exists and is non-empty.</small></p>";