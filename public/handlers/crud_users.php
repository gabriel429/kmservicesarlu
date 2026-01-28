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
                $nom = $_POST['nom'] ?? '';
                $prenom = $_POST['prenom'] ?? '';
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                $role = $_POST['role'] ?? 'editor';
                
                if (empty($nom) || empty($email) || empty($password)) {
                    throw new Exception('Nom, email et mot de passe sont requis');
                }
                
                // Générer un username basé sur l'email
                $username = strtolower(explode('@', $email)[0]);
                
                // Vérifier si l'email existe déjà
                $existing = MySQLCore::fetch(
                    "SELECT id FROM users WHERE email = ?",
                    [$email]
                );
                
                if ($existing) {
                    throw new Exception('Cet email existe déjà');
                }
                
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                
                // Gérer l'upload de photo
                $photoPath = null;
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = UPLOAD_DIR . 'users/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
                    $filePath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
                        $photoPath = 'users/' . $fileName;
                    }
                }
                
                MySQLCore::execute(
                    "INSERT INTO users (username, email, password, role, nom, prenom, photo) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$username, $email, $hashedPassword, $role, $nom, $prenom, $photoPath]
                );
                
                $userId = MySQLCore::lastInsertId();
                $response = [
                    'success' => true,
                    'message' => 'Utilisateur créé avec succès',
                    'id' => $userId
                ];
                logUserAction('create_user', $userId, json_encode(['email' => $email, 'nom' => $nom]));
            }
            break;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
                    throw new Exception('Accès refusé: réservé aux administrateurs');
                }
                $id = $_POST['id'] ?? 0;
                $nom = $_POST['nom'] ?? '';
                $prenom = $_POST['prenom'] ?? '';
                $email = $_POST['email'] ?? '';
                $role = $_POST['role'] ?? 'editor';
                $password = $_POST['password'] ?? '';
                $active = $_POST['active'] ?? 1;
                
                if (!$id) {
                    throw new Exception('ID de l\'utilisateur requis');
                }
                
                // Récupérer les données actuelles
                $user = MySQLCore::fetch("SELECT photo FROM users WHERE id = ?", [$id]);
                $photoPath = $user['photo'];
                
                // Gérer l'upload de photo si présent
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = UPLOAD_DIR . 'users/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    // Supprimer l'ancienne photo
                    if ($photoPath && file_exists(UPLOAD_DIR . $photoPath)) {
                        unlink(UPLOAD_DIR . $photoPath);
                    }
                    
                    $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
                    $filePath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
                        $photoPath = 'users/' . $fileName;
                    }
                }
                
                if ($password) {
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    MySQLCore::execute(
                        "UPDATE users SET nom = ?, prenom = ?, email = ?, role = ?, password = ?, active = ?, photo = ? 
                         WHERE id = ?",
                        [$nom, $prenom, $email, $role, $hashedPassword, $active, $photoPath, $id]
                    );
                } else {
                    MySQLCore::execute(
                        "UPDATE users SET nom = ?, prenom = ?, email = ?, role = ?, active = ?, photo = ? 
                         WHERE id = ?",
                        [$nom, $prenom, $email, $role, $active, $photoPath, $id]
                    );
                }
                
                $response = ['success' => true, 'message' => 'Utilisateur mis à jour avec succès'];
                logUserAction('update_user', $id, json_encode(['nom' => $nom, 'email' => $email, 'role' => $role]));
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
                "SELECT id, username, email, nom, prenom, role, active, photo FROM users WHERE id = ?",
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
