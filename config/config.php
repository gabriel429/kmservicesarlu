<?php
if (!defined('DB_HOST')) define('DB_HOST', 'srv996.hstgr.io');
if (!defined('DB_NAME')) define('DB_NAME', 'u424760992_kmservices');
if (!defined('DB_USER')) define('DB_USER', 'u424760992_kmservices_use');
if (!defined('DB_PASS')) define('DB_PASS', 'Kmservices@@Kin243');

// Détection précise des chemins
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']); // ex: /kmservices/public/index.php ou /index.php

if (str_contains($scriptName, '/public/')) {
    // Cas WAMP : dossier /public/ visible dans l'URL du script
    $baseDir = str_replace('/public', '', dirname($scriptName));
    $assetDir = dirname($scriptName);
} else {
    // Cas Serveur PHP intégré (-t public) ou VirtualHost vers public/
    $baseDir = dirname($scriptName);
    $assetDir = dirname($scriptName);
}

$baseDir = rtrim($baseDir, '/') . '/';
$assetDir = rtrim($assetDir, '/') . '/';

if (!defined('APP_URL')) define('APP_URL', $protocol . '://' . $host . $baseDir);
if (!defined('ASSET_URL')) define('ASSET_URL', $protocol . '://' . $host . $assetDir);

if (!defined('APP_NAME')) define('APP_NAME', 'KM Services');
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');

// Auto-initialisation des tables manquantes
if (!defined('DB_INIT_DONE')) {
    define('DB_INIT_DONE', true);
    try {
        require_once __DIR__ . '/../app/MySQL.php';
        
        // Créer la table quote_requests si elle n'existe pas
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
                treated_by INT DEFAULT NULL,
                FOREIGN KEY (treated_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_statut (statut),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    } catch (Throwable $e) {
        // Table exists or initialization not possible yet
    }
}