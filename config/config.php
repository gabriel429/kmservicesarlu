<?php
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'kmservices');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// Détection précise des chemins
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']); // ex: /kmservices/public/index.php ou /index.php

if (str_contains($scriptName, '/public/')) {
    // Cas WAMP : dossier /public/ visible dans l'URL du script
    $baseDir = str_replace('/public', '', dirname($scriptName));
    $assetDir = dirname($scriptName);
} else {
    // Cas Serveur PHP intégré (-t public) ou VirtualHost vers public/
    $baseDir = dirname($scriptName);
    $assetDir = dirname($scriptName);
}

$baseDir = rtrim($baseDir, '/') . '/';
$assetDir = rtrim($assetDir, '/') . '/';

if (!defined('APP_URL')) define('APP_URL', $protocol . '://' . $host . $baseDir);
if (!defined('ASSET_URL')) define('ASSET_URL', $protocol . '://' . $host . $assetDir);

if (!defined('APP_NAME')) define('APP_NAME', 'KM Services');
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
