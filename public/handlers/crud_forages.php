<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(0);
ini_set('display_errors', 0);
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

        default:
            throw new Exception('Action invalide: ' . $action);
    }
} catch (Throwable $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
exit;
