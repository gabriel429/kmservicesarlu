<?php
// Simulate an admin product creation via HTTP multipart POST to the handler
$url = $argv[1] ?? 'http://localhost/kmservices/public/handlers/crud_products.php';

// Load DB helpers to create/update a test admin user
chdir(__DIR__ . '/..');
// PHP 7.4 compatibility: polyfill for str_contains si nécessaire
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool {
        return $needle === '' ? true : strpos($haystack, $needle) !== false;
    }
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/MySQL.php';

$testUsername = 'test_admin';
$testPassword = 'TestPass123!';

try {
    $existing = MySQLCore::fetch("SELECT id FROM users WHERE username = ?", [$testUsername]);
    $hash = password_hash($testPassword, PASSWORD_DEFAULT);
    if ($existing) {
        MySQLCore::execute("UPDATE users SET password = ?, role = ? WHERE id = ?", [$hash, 'admin', $existing['id']]);
    } else {
        // Try insert with common columns; avoid columns that may not exist
        MySQLCore::execute("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)", [$testUsername, $testUsername . '@example.com', $hash, 'admin']);
    }
} catch (Throwable $e) {
    echo "DEBUG_DB_WRITE_ERROR:" . $e->getMessage() . "\n";
}
// (debug prints removed)

$sample = __DIR__ . '/../public/uploads/products/product_sample_1.png';
if (!file_exists($sample)) {
    // create a tiny sample image
    $im = imagecreatetruecolor(200,120);
    imagesavealpha($im, true);
    $trans = imagecolorallocatealpha($im,0,0,0,127);
    imagefill($im,0,0,$trans);
    $col = imagecolorallocate($im,80,140,200);
    imagestring($im,5,10,40,'http-test',$col);
    imagepng($im,$sample);
    imagedestroy($im);
}

$handlersBase = rtrim(dirname($url), '/\\');
$loginUrl = $handlersBase . '/login.php';

$cookieFile = sys_get_temp_dir() . '/km_http_cookie.txt';
@unlink($cookieFile);

// Perform JSON login to obtain session cookie
$ch = curl_init($loginUrl);
$payload = json_encode(['username' => $testUsername, 'password' => $testPassword]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$loginResp = curl_exec($ch);
if ($loginResp === false) {
    echo json_encode(['success' => false, 'error' => 'Login request failed: ' . curl_error($ch)]);
    exit(1);
}
$loginCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$loginData = json_decode($loginResp, true);
if (!($loginData['success'] ?? false)) {
    echo json_encode(['success' => false, 'phase' => 'login', 'http_code' => $loginCode, 'response' => $loginData], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit(1);
}

// Now perform multipart/form-data POST with the session cookie
$cfile = new CURLFile($sample, 'image/png', basename($sample));
$post = [
    'action' => 'create',
    'nom' => 'HTTP Test Product ' . time(),
    'description' => 'Créé par http_upload_simulator (auth)',
    'prix' => '14.50',
    'stock' => '3',
    'actif' => '1',
    'image' => $cfile
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$resp = curl_exec($ch);
if ($resp === false) {
    $err = curl_error($ch);
    echo json_encode(['success' => false, 'error' => $err], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit(1);
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo json_encode(['success' => true, 'http_code' => $httpCode, 'login_response' => $loginData, 'response' => json_decode($resp, true)], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
