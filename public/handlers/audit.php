<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(0);
ini_set('display_errors', 0);
session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/MySQL.php';

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
        throw new Exception('Accès refusé: réservé aux administrateurs');
    }

    $type = $_GET['type'] ?? 'users'; // users|products|projects|contacts
    $limit = intval($_GET['limit'] ?? 20);
    $offset = intval($_GET['offset'] ?? 0);

    switch ($type) {
        case 'users':
            $rows = MySQLCore::fetchAll(
                "SELECT id, admin_user_id, target_user_id, action, details, created_at FROM user_audit ORDER BY id DESC LIMIT ? OFFSET ?",
                [$limit, $offset]
            );
            break;
        case 'products':
            $rows = MySQLCore::fetchAll(
                "SELECT id, admin_user_id, product_id, action, details, created_at FROM product_audit ORDER BY id DESC LIMIT ? OFFSET ?",
                [$limit, $offset]
            );
            break;
        case 'projects':
            $rows = MySQLCore::fetchAll(
                "SELECT id, admin_user_id, project_id, action, details, created_at FROM project_audit ORDER BY id DESC LIMIT ? OFFSET ?",
                [$limit, $offset]
            );
            break;
        case 'contacts':
            $rows = MySQLCore::fetchAll(
                "SELECT id, admin_user_id, contact_id, action, details, created_at FROM contact_audit ORDER BY id DESC LIMIT ? OFFSET ?",
                [$limit, $offset]
            );
            break;
        case 'security':
            // Reutilise la table user_audit pour les événements de sécurité (forbidden_access)
            $rows = MySQLCore::fetchAll(
                "SELECT id, admin_user_id, NULL AS target_user_id, action, details, created_at FROM user_audit WHERE action = 'forbidden_access' ORDER BY id DESC LIMIT ? OFFSET ?",
                [$limit, $offset]
            );
            break;
        default:
            throw new Exception('Type invalide');
    }

    $response = ['success' => true, 'data' => $rows];
} catch (Throwable $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
exit;
