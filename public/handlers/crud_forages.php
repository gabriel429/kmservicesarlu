<?php
header('Content-Type: application/json; charset=UTF-8');
// Activer les erreurs pour le débogage des requêtes AJAX
ini_set('display_errors', 0); // On ne veut pas polluer le JSON
error_reporting(E_ALL);

session_start();

$response = ['success' => false, 'message' => 'Erreur'];

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/MySQL.php';

try {
    // Sécurité minimale: requiert une session admin connectée
    if (!isset($_SESSION['admin_user_id'])) {
        throw new Exception('Non autorisé');
    }

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            if (!$id) throw new Exception('ID requis');

            // Assurer l'existence de la table si nécessaire (idempotent)
            MySQLCore::execute("CREATE TABLE IF NOT EXISTS drilling_requests (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nom VARCHAR(150) NOT NULL,
                email VARCHAR(150) NOT NULL,
                telephone VARCHAR(20) NOT NULL,
                type_forage VARCHAR(100),
                profondeur_estimee INT,
                localisation VARCHAR(255) NOT NULL,
                description LONGTEXT,
                delai_souhaite VARCHAR(100),
                budget_estime DECIMAL(12,2),
                document_joint VARCHAR(255),
                statut ENUM('nouveau','en_attente','contacte','complete','refuse') DEFAULT 'nouveau',
                lu TINYINT DEFAULT 0,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                treated_by INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $sql = "SELECT id, nom, email, telephone, type_forage, localisation, description, delai_souhaite, budget_estime, document_joint, statut, created_at FROM drilling_requests WHERE id = ?";
            $item = MySQLCore::fetch($sql, [$id]);
            if (!$item) throw new Exception('Demande non trouvée');

            $response = ['success' => true, 'data' => $item];
            break;

        case 'create':
            $localisation = $_POST['localisation'] ?? '';
            $profondeur_estimee = intval($_POST['profondeur_estimee'] ?? 0);
            $statut = $_POST['statut'] ?? 'nouveau';
            
            if (!$localisation) throw new Exception('Localisation requise');
            
            $sql = "INSERT INTO drilling_requests (localisation, profondeur_estimee, statut) VALUES (?, ?, ?)";
            MySQLCore::execute($sql, [$localisation, $profondeur_estimee, $statut]);
            
            $response = ['success' => true, 'message' => 'Demande créée'];
            break;

        case 'update':
            $id = intval($_POST['id'] ?? 0);
            $localisation = $_POST['localisation'] ?? '';
            $profondeur_estimee = intval($_POST['profondeur_estimee'] ?? 0);
            $statut = $_POST['statut'] ?? '';
            
            if (!$id || !$localisation) throw new Exception('Données requises');
            
            $sql = "UPDATE drilling_requests SET localisation = ?, profondeur_estimee = ?, statut = ? WHERE id = ?";
            MySQLCore::execute($sql, [$localisation, $profondeur_estimee, $statut, $id]);
            
            $response = ['success' => true, 'message' => 'Demande mise à jour'];
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            if (!$id) throw new Exception('ID requis');
            
            $sql = "DELETE FROM drilling_requests WHERE id = ?";
            MySQLCore::execute($sql, [$id]);
            
            $response = ['success' => true, 'message' => 'Demande supprimée'];
            break;

        default:
            throw new Exception('Action invalide: ' . $action);
    }
} catch (Throwable $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
exit;
