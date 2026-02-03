<?php
require_once __DIR__ . '/config.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function format_price(float $price): string
{
    return number_format($price, 2, ',', ' ') . ' $';
}

function current_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION[CSRF_TOKEN_KEY])) {
        $_SESSION[CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
    }

    return $_SESSION[CSRF_TOKEN_KEY];
}

function csrf_verify(?string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return !empty($token) && isset($_SESSION[CSRF_TOKEN_KEY]) && hash_equals($_SESSION[CSRF_TOKEN_KEY], $token);
}

function log_activity(string $action, ?string $context = null): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $userId = $_SESSION['user']['id'] ?? null;
    $pdo = getPDO();
    $stmt = $pdo->prepare('INSERT INTO journal_activite (utilisateur_id, action, contexte) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $action, $context]);
}

function handle_image_upload(string $fieldName, string $targetDir = null): ?string
{
    if (empty($_FILES[$fieldName]['name'])) {
        return null;
    }

    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        return null;
    }

    $targetDir = $targetDir ?? __DIR__ . '/../assets/images/uploads';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $fileName = uniqid('img_', true) . '.' . $ext;
    $targetPath = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return null;
    }

    return 'assets/images/uploads/' . $fileName;
}

function partners_data_path(): string
{
    return __DIR__ . '/../data/partners.json';
}

function read_partners(): array
{
    $path = partners_data_path();
    if (!file_exists($path)) {
        return [
            ['name' => 'KM SERVICES', 'logo' => 'assets/images/logoKMS.png', 'url' => '#'],
        ];
    }

    $content = file_get_contents($path);
    $decoded = json_decode($content ?: '[]', true);
    if (!is_array($decoded)) {
        return [];
    }

    return $decoded;
}

function save_partners(array $partners): bool
{
    $path = partners_data_path();
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $payload = json_encode(array_values($partners), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return $payload !== false && file_put_contents($path, $payload) !== false;
}
