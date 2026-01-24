<?php
/**
 * Handler CRUD pour les Utilisateurs
 */

session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/MySQL.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$response = ['success' => false, 'message' => ''];

function ensureAuditTable() {
    MySQLCore::execute(
        "CREATE TABLE IF NOT EXISTS user_audit (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_user_id INT NULL,
            target_user_id INT NULL,
            action VARCHAR(50) NOT NULL,
            details TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function logUserAction($action, $targetId = null, $details = null) {
    ensureAuditTable();
    $adminId = $_SESSION['admin_user_id'] ?? null;
    MySQLCore::execute(
        "INSERT INTO user_audit (admin_user_id, target_user_id, action, details) VALUES (?, ?, ?, ?)",
        [$adminId, $targetId, $action, $details]
    );
}

try {
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
                    throw new Exception('Accès refusé: réservé aux administrateurs');
                }
                $username = $_POST['username'] ?? '';
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                $role = $_POST['role'] ?? 'editor';
                
                if (empty($username) || empty($email) || empty($password)) {
                    throw new Exception('Username, email et mot de passe sont requis');
                }
                
                // Vérifier si l'utilisateur existe déjà
                $existing = MySQLCore::fetch(
                    "SELECT id FROM users WHERE username = ? OR email = ?",
                    [$username, $email]
                );
                
                if ($existing) {
                    throw new Exception('Cet utilisateur ou cet email existe déjà');
                }
                
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                
                MySQLCore::execute(
                    "INSERT INTO users (username, email, password, role) 
                     VALUES (?, ?, ?, ?)",
                    [$username, $email, $hashedPassword, $role]
                );
                
                $response = [
                    'success' => true,
                    'message' => 'Utilisateur créé avec succès',
                    'id' => MySQLCore::lastInsertId()
                ];
                logUserAction('create_user', $response['id'], json_encode(['username' => $username, 'email' => $email]));
            }
            break;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
                    throw new Exception('Accès refusé: réservé aux administrateurs');
                }
                $id = $_POST['id'] ?? 0;
                $username = $_POST['username'] ?? '';
                $email = $_POST['email'] ?? '';
                $role = $_POST['role'] ?? 'editor';
                $password = $_POST['password'] ?? '';
                
                if (!$id) {
                    throw new Exception('ID de l\'utilisateur requis');
                }
                
                if ($password) {
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    MySQLCore::execute(
                        "UPDATE users SET username = ?, email = ?, role = ?, password = ? 
                         WHERE id = ?",
                        [$username, $email, $role, $hashedPassword, $id]
                    );
                } else {
                    MySQLCore::execute(
                        "UPDATE users SET username = ?, email = ?, role = ? 
                         WHERE id = ?",
                        [$username, $email, $role, $id]
                    );
                }
                
                $response = ['success' => true, 'message' => 'Utilisateur mis à jour avec succès'];
                logUserAction('update_user', $id, json_encode(['username' => $username, 'email' => $email, 'role' => $role]));
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
                    throw new Exception('Accès refusé: réservé aux administrateurs');
                }
                $id = $_POST['id'] ?? 0;
                
                if (!$id) {
                    throw new Exception('ID de l\'utilisateur requis');
                }
                
                // Ne pas supprimer l'utilisateur actuellement connecté
                if ($id == $_SESSION['admin_user_id']) {
                    throw new Exception('Vous ne pouvez pas supprimer votre propre compte');
                }
                
                MySQLCore::execute("DELETE FROM users WHERE id = ?", [$id]);
                
                $response = ['success' => true, 'message' => 'Utilisateur supprimé avec succès'];
                logUserAction('delete_user', $id, null);
            }
            break;
            
        case 'get':
            if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
                throw new Exception('Accès refusé: réservé aux administrateurs');
            }
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                throw new Exception('ID de l\'utilisateur requis');
            }
            
            $user = MySQLCore::fetch(
                "SELECT id, username, email, role FROM users WHERE id = ?",
                [$id]
            );
            $response = [
                'success' => true,
                'data' => $user
            ];
            break;

        case 'get_by_username':
            if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
                throw new Exception('Accès refusé: réservé aux administrateurs');
            }
            $username = $_GET['username'] ?? '';
            if (!$username) {
                throw new Exception('Username requis');
            }

            $user = MySQLCore::fetch(
                "SELECT id, username, email, role FROM users WHERE username = ? OR email = ?",
                [$username, $username]
            );
            if ($user) {
                $response = [
                    'success' => true,
                    'data' => $user
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ];
            }
            break;

        case 'reset_password':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
                    throw new Exception('Accès refusé: réservé aux administrateurs');
                }
                $identifier = $_POST['identifier'] ?? ''; // username ou email
                $newPassword = $_POST['password'] ?? ($_POST['new_password'] ?? '');

                if (empty($identifier) || empty($newPassword)) {
                    throw new Exception('Identifiant et nouveau mot de passe requis');
                }

                $user = MySQLCore::fetch(
                    "SELECT id FROM users WHERE username = ? OR email = ?",
                    [$identifier, $identifier]
                );

                if (!$user) {
                    throw new Exception('Utilisateur non trouvé');
                }

                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                MySQLCore::execute(
                    "UPDATE users SET password = ? WHERE id = ?",
                    [$hashedPassword, $user['id']]
                );

                $response = [
                    'success' => true,
                    'message' => 'Mot de passe réinitialisé avec succès'
                ];
                logUserAction('reset_password', $user['id'], json_encode(['identifier' => $identifier]));
            }
            break;
            
        default:
            throw new Exception('Action non reconnue');
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
