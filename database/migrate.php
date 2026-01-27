<?php
/**
 * Script de migration - Force la création de toutes les tables nécessaires
 * À exécuter une seule fois sur le serveur de production
 * 
 * Usage: Accéder à /database/migrate.php via le navigateur ou l'appel curl
 */

// Désactiver l'affichage des erreurs PHP pour éviter les expositions de chemin
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Enregistrer les erreurs dans un fichier
$error_log = dirname(__FILE__) . '/migration.log';
ini_set('log_errors', 1);
ini_set('error_log', $error_log);

try {
    // Connexion à la base de données
    require_once dirname(__FILE__, 2) . '/config/config.php';
    require_once dirname(__FILE__, 2) . '/app/MySQL.php';

    $results = [];

    // 1. Créer la table quote_requests si elle n'existe pas
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
                treated_by INT DEFAULT NULL,
                FOREIGN KEY (treated_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_statut (statut),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $results['quote_requests'] = 'Créée ou existe déjà ✓';
    } catch (Throwable $e) {
        $results['quote_requests'] = 'Erreur: ' . $e->getMessage();
    }

    // 2. Vérifier que la table orders existe
    try {
        MySQLCore::execute("DESCRIBE orders");
        $results['orders'] = 'Existe ✓';
    } catch (Throwable $e) {
        $results['orders'] = 'N\'existe pas - création nécessaire';
        try {
            MySQLCore::execute(
                "CREATE TABLE IF NOT EXISTS orders (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    numero_commande VARCHAR(50) UNIQUE NOT NULL,
                    nom VARCHAR(150) NOT NULL,
                    email VARCHAR(150) NOT NULL,
                    telephone VARCHAR(20) NOT NULL,
                    localisation VARCHAR(255),
                    quantite_totale INT,
                    prix_total DECIMAL(12, 2),
                    statut ENUM('nouveau', 'confirme', 'expediee', 'livree', 'annulee') DEFAULT 'nouveau',
                    notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_statut (statut),
                    INDEX idx_email (email)
                )"
            );
            $results['orders'] = 'Créée ✓';
        } catch (Throwable $e2) {
            $results['orders'] = 'Erreur création: ' . $e2->getMessage();
        }
    }

    // 3. Vérifier que la table order_items existe
    try {
        MySQLCore::execute("DESCRIBE order_items");
        $results['order_items'] = 'Existe ✓';
    } catch (Throwable $e) {
        $results['order_items'] = 'N\'existe pas - création nécessaire';
        try {
            MySQLCore::execute(
                "CREATE TABLE IF NOT EXISTS order_items (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    order_id INT NOT NULL,
                    product_id INT NOT NULL,
                    quantite INT DEFAULT 1,
                    prix_unitaire DECIMAL(10, 2) NOT NULL,
                    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
                )"
            );
            $results['order_items'] = 'Créée ✓';
        } catch (Throwable $e2) {
            $results['order_items'] = 'Erreur création: ' . $e2->getMessage();
        }
    }

    // 4. Vérifier que contacts existe
    try {
        MySQLCore::execute("DESCRIBE contacts");
        $results['contacts'] = 'Existe ✓';
    } catch (Throwable $e) {
        $results['contacts'] = 'N\'existe pas - création nécessaire';
    }

    // Retourner le résultat en JSON
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => 'Migration terminée',
        'results' => $results,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    // Enregistrer l'erreur
    error_log("Migration Error: " . $e->getMessage());
}
?>
