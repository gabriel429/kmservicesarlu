<?php
/**
 * Gestionnaire des Paramètres du Site
 */

header('Content-Type: application/json; charset=utf-8');

// Vérifier l'authentification
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

// Vérifier les droits (admin seulement)
if (($_SESSION['admin_role'] ?? 'editor') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès restreint']);
    exit;
}

if (!class_exists('MySQLCore')) {
    require_once dirname(__DIR__, 2) . '/app/MySQL.php';
}

$action = $_POST['action'] ?? '';

if ($action === 'save_settings') {
    try {
        // Créer la table des paramètres si elle n'existe pas
        MySQLCore::execute(
            "CREATE TABLE IF NOT EXISTS site_settings (
                id INT PRIMARY KEY AUTO_INCREMENT,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value LONGTEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_key (setting_key)
            )"
        );
        
        // Récupérer les paramètres du formulaire
        $settings = [
            'site_name' => $_POST['site_name'] ?? 'KM Services',
            'site_email' => $_POST['site_email'] ?? '',
            'site_phone' => $_POST['site_phone'] ?? '',
            'site_address' => $_POST['site_address'] ?? '',
            'site_description' => $_POST['site_description'] ?? '',
            'site_keywords' => $_POST['site_keywords'] ?? ''
        ];
        
        // Sauvegarder chaque paramètre
        foreach ($settings as $key => $value) {
            // Utiliser INSERT ... ON DUPLICATE KEY UPDATE pour créer ou mettre à jour
            MySQLCore::execute(
                "INSERT INTO site_settings (setting_key, setting_value) 
                 VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                [$key, $value]
            );
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Paramètres sauvegardés avec succès'
        ]);
        
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
}
?>
