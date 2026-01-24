<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/MySQL.php';
header('Content-Type: application/json; charset=utf-8');
if (!defined('APP_ENV') || APP_ENV !== 'development') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}
try {
    $rows = MySQLCore::fetchAll("SELECT id, nom, slug, prix, image_principale, actif FROM products ORDER BY id ASC");
    echo json_encode(['ok' => true, 'count' => count($rows), 'items' => $rows]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
