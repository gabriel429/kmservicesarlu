<?php
/**
 * Gestionnaire des Paramètres du Site
 */

// Configurer les en-têtes CORS pour les requêtes AJAX
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Méthode 1: Vérifier via session (si cookies transmis)
$authenticated = false;
$admin_role = null;

if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_role'])) {
    $authenticated = true;
    $admin_role = $_SESSION['admin_role'];
}

// Méthode 2: Vérifier via Authorization header (pour domaines différents)
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_POST['auth_token'] ?? '';
if (!$authenticated && !empty($auth_header)) {
    // Vérifier le token
    if (preg_match('/Bearer\s+(.+)/i', $auth_header, $matches)) {
        $token = $matches[1];
    } else {
        $token = $auth_header;
    }
    
    // Valider le token (simple vérification)
    if (!empty($token) && strlen($token) > 10) {
        // Chercher dans la session stockée ou base de données
        $authenticated = true;
        $admin_role = 'admin'; // À améliorer avec vrai token
    }
}

// Si pas authentifié du tout, vérifier si admin est en GET pour debug
if (!$authenticated && isset($_GET['debug_admin'])) {
    error_log("DEBUG: Tentative sans authentification depuis " . $_SERVER['REMOTE_ADDR']);
    error_log("DEBUG: SESSION: " . json_encode($_SESSION));
    error_log("DEBUG: AUTH_HEADER: " . $auth_header);
    error_log("DEBUG: POST: " . json_encode($_POST));
}

if (!$authenticated) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'Non authentifié - veuillez vous reconnecter',
        'session_id' => session_id(),
        'has_session' => !empty($_SESSION)
    ]);
    exit;
}

// Vérifier les droits (admin seulement)
if ($admin_role !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès restreint - admin requis']);
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
