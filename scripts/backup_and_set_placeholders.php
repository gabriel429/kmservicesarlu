<?php
// Sauvegarde les valeurs image_principale non trouvées localement et les remplace par des placeholders
error_reporting(E_ALL);
ini_set('display_errors', 1);

$dbHost = 'srv996.hstgr.io';
$dbUser = 'u424760992_kmservices_use';
$dbPass = 'Kmservices@@Kin243';
$dbName = 'u424760992_kmservices';
$dbPort = 3306;

$mysqli = mysqli_init();
mysqli_options($mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, 5);
if (!@$mysqli->real_connect($dbHost, $dbUser, $dbPass, $dbName, $dbPort)) {
    fwrite(STDERR, "DB connect failed: " . mysqli_connect_error() . "\n");
    exit(1);
}

function exists_local($val) {
    $val = trim((string)$val);
    if ($val === '') return false;
    if (preg_match('#^https?://#i', $val)) return false;
    // normalize
    $v = preg_replace('#^(/?public/)?/?uploads/#i', '', $val);
    $base = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads');
    $candidates = [
        $base . DIRECTORY_SEPARATOR . $v,
        $base . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . basename($v),
        $base . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . basename($v),
    ];
    foreach ($candidates as $p) {
        if (is_file($p)) return realpath($p);
    }
    return false;
}

$backup = ['products' => [], 'projects' => []];
$updated = ['products' => 0, 'projects' => 0];

// Process products
$res = $mysqli->query("SELECT id, image_principale FROM products WHERE image_principale IS NOT NULL AND image_principale != ''");
while ($r = $res->fetch_assoc()) {
    $exists = exists_local($r['image_principale']);
    if (!$exists) {
        $backup['products'][] = $r;
        $stmt = $mysqli->prepare("UPDATE products SET image_principale = ? WHERE id = ?");
        $placeholder = 'products/product_sample_1.png';
        $stmt->bind_param('si', $placeholder, $r['id']);
        if ($stmt->execute()) $updated['products']++;
        $stmt->close();
    }
}

// Process projects
$res = $mysqli->query("SELECT id, image_principale FROM projects WHERE image_principale IS NOT NULL AND image_principale != ''");
while ($r = $res->fetch_assoc()) {
    $exists = exists_local($r['image_principale']);
    if (!$exists) {
        $backup['projects'][] = $r;
        $stmt = $mysqli->prepare("UPDATE projects SET image_principale = ? WHERE id = ?");
        $placeholder = 'projects/project_sample_1.png';
        $stmt->bind_param('si', $placeholder, $r['id']);
        if ($stmt->execute()) $updated['projects']++;
        $stmt->close();
    }
}

$ts = date('Ymd_His');
$backupFile = __DIR__ . DIRECTORY_SEPARATOR . 'backup_image_principale_' . $ts . '.json';
file_put_contents($backupFile, json_encode(['backup' => $backup, 'updated' => $updated], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "Backup written to: $backupFile\n";
echo "Products updated: " . $updated['products'] . "\n";
echo "Projects updated: " . $updated['projects'] . "\n";

$mysqli->close();
exit(0);
