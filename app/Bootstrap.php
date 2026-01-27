<?php
/**
 * Bootstrap d'initialisation - Crée les tables manquantes automatiquement
 * À inclure dans tous les fichiers d'entrée (index.php, handlers, etc.)
 */

// Vérifier si déjà exécuté
if (defined('DB_TABLES_INITIALIZED')) {
    return;
}
define('DB_TABLES_INITIALIZED', true);

try {
    if (!class_exists('MySQLCore')) {
        return; // MySQLCore pas encore chargé
    }

    // Créer la table quote_requests
    @MySQLCore::execute(
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
            treated_by INT DEFAULT NULL,
            FOREIGN KEY (treated_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_statut (statut),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
} catch (Throwable $e) {
    // Silencieux - la table peut déjà exister
    // ou nous ne pouvons pas créer à ce stade
}
?>
