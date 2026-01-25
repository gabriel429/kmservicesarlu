<?php
// Forcer le type de contenu JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Désactiver les erreurs HTML
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/MySQL.php';

// Démarrer la session
session_start();

try {
    // Lire les données JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Données JSON invalides']);
        exit;
    }
    
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    
    // Validation simple pour le test
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs']);
        exit;
    }
    
    // Connexion via MySQLi pur
    $user = MySQLCore::fetch(
        "SELECT id, username, password, role FROM users WHERE username = ? OR email = ?",
        [$username, $username]
    );
    
    // Ensure audit table exists
    try {
        MySQLCore::execute("CREATE TABLE IF NOT EXISTS user_audit (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_user_id INT NULL,
            target_user_id INT NULL,
            action VARCHAR(50) NOT NULL,
            details TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $e) { /* ignore */ }

    if ($user && password_verify($password, $user['password'])) {
        // La session est déjà démarrée
        $_SESSION['admin_user_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role'] = $user['role'];
        // Log login_success
        try {
            $details = json_encode([
                'username' => $username,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            MySQLCore::execute(
                "INSERT INTO user_audit (admin_user_id, target_user_id, action, details) VALUES (?, NULL, 'login_success', ?)",
                [$user['id'], $details]
            );
        } catch (Throwable $e) { /* ignore */ }
        
        echo json_encode(['success' => true, 'message' => 'Connexion réussie', 'redirect' => '/admin/dashboard']);
    } else {
        // Log login_failed
        try {
            $details = json_encode([
                'username' => $username,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            MySQLCore::execute(
                "INSERT INTO user_audit (admin_user_id, target_user_id, action, details) VALUES (NULL, NULL, 'login_failed', ?)",
                [$details]
            );
        } catch (Throwable $e) { /* ignore */ }
        echo json_encode([
            'success' => false,
            'message' => 'Identifiant ou mot de passe incorrect'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
?>
