<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Paramètres de connexion — fournis par l'utilisateur
$dbHost = 'srv996.hstgr.io';
$dbUser = 'u424760992_kmservices_use';
$dbPass = 'Kmservices@@Kin243';
$dbName = 'u424760992_kmservices';
$dbPort = 3306;

// Initialiser avec timeout de connexion pour éviter les blocages
$mysqli = mysqli_init();
mysqli_options($mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, 5);
if (!@$mysqli->real_connect($dbHost, $dbUser, $dbPass, $dbName, $dbPort)) {
    http_response_code(500);
    echo json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]);
    exit(1);
}

$queries = [
    'products' => "SELECT id, nom, image_principale FROM products WHERE image_principale IS NOT NULL AND image_principale != '' LIMIT 20",
    'projects' => "SELECT id, titre, image_principale FROM projects WHERE image_principale IS NOT NULL AND image_principale != '' LIMIT 20",
];

$result = [];
foreach ($queries as $key => $sql) {
    $res = $mysqli->query($sql);
    if ($res === false) {
        $result[$key] = ['error' => $mysqli->error];
        continue;
    }
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    $result[$key] = $rows;
    $res->free();
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$mysqli->close();

// Exit
exit(0);

?>
