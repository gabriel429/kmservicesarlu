<?php
// Health check pour l'environnement Vercel + Supabase
// Retourne un JSON avec l'état de la connexion DB et la configuration Storage

header('Content-Type: application/json');

$result = [
    'db' => [
        'ok' => false,
        'driver' => null,
        'host' => null,
        'port' => null,
        'name' => null,
    ],
    'storage' => [
        'configured' => false,
        'public_bucket' => null,
        'url' => null,
    ],
];

try {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../app/Database.php';
    require_once __DIR__ . '/../app/Supabase.php';

    // DB
    $result['db']['driver'] = defined('DB_DRIVER') ? DB_DRIVER : 'mysql';
    $result['db']['host'] = defined('DB_HOST') ? DB_HOST : null;
    $result['db']['port'] = defined('DB_PORT') ? DB_PORT : null;
    $result['db']['name'] = defined('DB_NAME') ? DB_NAME : null;
    try {
        // Connecter directement via PDO pour éviter un die() global
        if ($result['db']['driver'] === 'pgsql') {
            $dsn = 'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';sslmode=require';
            $pdo = new \PDO($dsn, DB_USER, DB_PASS, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } else {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new \PDO($dsn, DB_USER, DB_PASS, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
        }
        $stmt = $pdo->query('SELECT 1');
        $stmt->fetch();
        $result['db']['ok'] = true;
    } catch (\Throwable $e) {
        $result['db']['ok'] = false;
        $result['db']['error'] = 'DB connection failed';
    }

    // Supabase Storage (ne pas exposer les clés)
    $supabaseUrl = defined('SUPABASE_URL') ? SUPABASE_URL : '';
    $bucket = defined('SUPABASE_BUCKET') ? SUPABASE_BUCKET : 'uploads';
    $serviceKeyPresent = defined('SUPABASE_SERVICE_ROLE_KEY') && SUPABASE_SERVICE_ROLE_KEY !== '';
    if ($supabaseUrl && $serviceKeyPresent) {
        $result['storage']['configured'] = true;
        $result['storage']['public_bucket'] = $bucket;
        // URL publique d'exemple (ne crée pas d'objet)
        $result['storage']['url'] = rtrim($supabaseUrl, '/') . '/storage/v1/object/public/' . rawurlencode($bucket) . '/';
    }
} catch (\Throwable $e) {
    // Ignorer, retourner au format JSON
}

echo json_encode($result);
?>
