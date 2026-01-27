<?php
/**
 * API pour vérifier et créer les tables de base
 * Retourne le statut de la base de données
 */

header('Content-Type: application/json; charset=utf-8');

try {
    require_once dirname(__FILE__, 2) . '/config/config.php';
    require_once dirname(__FILE__, 2) . '/app/MySQL.php';

    $tables_to_check = ['quote_requests', 'orders', 'order_items', 'contacts', 'projects', 'products', 'drilling_requests'];
    $status = [];

    // Vérifier chaque table
    foreach ($tables_to_check as $table) {
        try {
            MySQLCore::execute("SELECT 1 FROM $table LIMIT 1");
            $status[$table] = ['exists' => true, 'accessible' => true];
        } catch (Throwable $e) {
            // Vérifier si c'est une erreur "table not found" ou autre
            $error_msg = $e->getMessage();
            if (stripos($error_msg, 'table') !== false && stripos($error_msg, 'not found') !== false) {
                $status[$table] = ['exists' => false, 'accessible' => false, 'error' => 'Table not found'];
            } else {
                $status[$table] = ['exists' => true, 'accessible' => false, 'error' => substr($error_msg, 0, 100)];
            }
        }
    }

    // Essayer de créer la table quote_requests si elle n'existe pas
    if (!$status['quote_requests']['exists']) {
        try {
            MySQLCore::execute(
                "CREATE TABLE IF NOT EXISTS quote_requests (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    numero_devis VARCHAR(50) UNIQUE NOT NULL,
                    nom VARCHAR(150) NOT NULL,
                    email VARCHAR(150) NOT NULL,
                    telephone VARCHAR(20) NOT NULL,
                    localisation VARCHAR(255),
                    service VARCHAR(100),
                    type_service VARCHAR(100),
                    description LONGTEXT,
                    delai_souhaite VARCHAR(100),
                    budget_estime DECIMAL(12, 2),
                    document_joint VARCHAR(255),
                    statut ENUM('nouveau', 'en_attente', 'contacte', 'accepte', 'refuse') DEFAULT 'nouveau',
                    lu TINYINT DEFAULT 0,
                    notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    treated_by INT,
                    FOREIGN KEY (treated_by) REFERENCES users(id) ON DELETE SET NULL,
                    INDEX idx_statut (statut),
                    INDEX idx_created_at (created_at)
                )"
            );
            $status['quote_requests'] = ['exists' => true, 'accessible' => true, 'created' => true];
        } catch (Throwable $e) {
            $status['quote_requests']['error'] = substr($e->getMessage(), 0, 200);
        }
    }

    echo json_encode([
        'success' => true,
        'database' => DB_NAME,
        'tables_status' => $status,
        'timestamp' => date('Y-m-d H:i:s'),
        'environment' => defined('PRODUCTION') ? 'production' : 'local'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>
