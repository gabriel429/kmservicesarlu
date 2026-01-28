<?php
/**
 * AUTO-INITIALISATION - Crée les tables manquantes au premier accès
 * Ce fichier s'auto-exécute et crée la table quote_requests garantie
 */

// Fichier de verrouillage pour éviter les exécutions répétées
$lockFile = __DIR__ . '/../app/init.lock';

// Si init.lock existe et est récent (moins de 24h), passer
if (file_exists($lockFile) && (time() - filemtime($lockFile) < 86400)) {
    return; // Déjà exécuté récemment
}

try {
    // Enregistrer le démarrage
    @mkdir(__DIR__ . '/../logs', 0755, true);
    $logFile = __DIR__ . '/../logs/init.log';
    
    // Obtenir les constantes DB
    if (!defined('DB_HOST')) {
        require_once __DIR__ . '/../config/config.php';
    }
    
    if (!defined('DB_HOST')) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " ERROR: DB constants not defined\n", FILE_APPEND);
        return;
    }
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " INIT: Starting table initialization\n", FILE_APPEND);
    
    // Connexion directe PDO
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]
        );
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " OK: Connected to database\n", FILE_APPEND);
        
        // Vérifier si la table existe
        $checkTable = $pdo->query("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA='" . DB_NAME . "' AND TABLE_NAME='quote_requests'");
        $tableExists = $checkTable && $checkTable->fetchColumn();
        
        if ($tableExists) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " OK: Table quote_requests already exists\n", FILE_APPEND);
        } else {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " CREATING: Table quote_requests\n", FILE_APPEND);
            
            // Créer la table
            $sql = "CREATE TABLE IF NOT EXISTS quote_requests (
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
                INDEX idx_treated_by (treated_by)
            )";
            
            $pdo->exec($sql);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " SUCCESS: Table quote_requests created\n", FILE_APPEND);
        }
        
        // Créer le fichier de verrouillage
        @file_put_contents($lockFile, time());
        
    } catch (PDOException $e) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " PDO ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
        throw $e;
    }
    
} catch (Throwable $e) {
    // Enregistrer l'erreur mais ne pas bloquer l'exécution
    @file_put_contents(
        __DIR__ . '/../logs/init.log',
        date('Y-m-d H:i:s') . " FATAL: " . $e->getMessage() . "\n",
        FILE_APPEND
    );
}
?>
