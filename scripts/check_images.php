<?php
// Vérifie si les valeurs image_principale en base existent localement
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
    $candidates = [
        __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $v,
        __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . basename($v),
        __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . basename($v),
    ];
    foreach ($candidates as $p) {
        if (is_file($p)) return realpath($p);
    }
    return false;
}

$out = ['products' => [], 'projects' => []];

$res = $mysqli->query("SELECT id, nom, image_principale FROM products WHERE image_principale IS NOT NULL AND image_principale != '' LIMIT 1000");
while ($r = $res->fetch_assoc()) {
    $exists = exists_local($r['image_principale']);
    $out['products'][] = ['id' => $r['id'], 'nom' => $r['nom'], 'image' => $r['image_principale'], 'local' => $exists ?: null];
}

$res = $mysqli->query("SELECT id, titre, image_principale FROM projects WHERE image_principale IS NOT NULL AND image_principale != '' LIMIT 1000");
while ($r = $res->fetch_assoc()) {
    $exists = exists_local($r['image_principale']);
    $out['projects'][] = ['id' => $r['id'], 'titre' => $r['titre'], 'image' => $r['image_principale'], 'local' => $exists ?: null];
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$mysqli->close();
exit(0);
