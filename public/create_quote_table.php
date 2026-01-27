<?php
/**
 * Force la création des tables manquantes
 * À exécuter une seule fois après déploiement
 */

// Bypass toute initialisation
set_error_handler(function() {});

try {
    // Connexion directe MySQL
    $pdo = new PDO(
        'mysql:host=srv996.hstgr.io;dbname=u424760992_kmservices;charset=utf8mb4',
        'u424760992_kmservices_use',
        'Kmservices@@Kin243'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Créer la table quote_requests
    $pdo->exec(
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
            treated_by INT NULL,
            INDEX idx_statut (statut),
            INDEX idx_created_at (created_at),
            INDEX idx_treated_by (treated_by),
            FOREIGN KEY (treated_by) REFERENCES users(id) ON DELETE SET NULL
        )"
    );

    header('Content-Type: application/json; charset=utf-8');
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Table quote_requests créée avec succès',
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} finally {
    restore_error_handler();
}
?>
