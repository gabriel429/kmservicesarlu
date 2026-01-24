<?php
require_once '../config/config.php';
require_once '../app/Database.php';

use App\Database;

echo "=== Test de Connexion à la Base de Données ===\n\n";

try {
    $db = Database::getInstance();
    echo "✅ Connexion réussie!\n\n";
    
    // Test 1: Vérifier les tables
    echo "--- Tables disponibles ---\n";
    $tables = $db->fetchAll("SHOW TABLES");
    if (!empty($tables)) {
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            echo "  ✓ " . $tableName . "\n";
        }
    } else {
        echo "  ❌ Aucune table trouvée\n";
    }
    
    echo "\n--- Utilisateurs ---\n";
    $users = $db->fetchAll("SELECT id, username, email, role FROM users LIMIT 5");
    if (!empty($users)) {
        echo "  Nombre d'utilisateurs: " . count($users) . "\n";
        foreach ($users as $user) {
            echo "    - {$user['username']} ({$user['email']}) - Rôle: {$user['role']}\n";
        }
    } else {
        echo "  ❌ Aucun utilisateur\n";
    }
    
    echo "\n--- Projets ---\n";
    $projects = $db->fetchAll("SELECT COUNT(*) as count FROM projects");
    echo "  Nombre de projets: " . $projects[0]['count'] . "\n";
    
    echo "\n--- Produits ---\n";
    $products = $db->fetchAll("SELECT COUNT(*) as count FROM products");
    echo "  Nombre de produits: " . $products[0]['count'] . "\n";
    
    echo "\n--- Services ---\n";
    $services = $db->fetchAll("SELECT COUNT(*) as count FROM services");
    echo "  Nombre de services: " . $services[0]['count'] . "\n";
    
    echo "\n✅ Tous les tests sont passés!\n";
    
} catch (Exception $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
    echo "\nDétails de la configuration:\n";
    echo "  Hôte: " . DB_HOST . "\n";
    echo "  Base: " . DB_NAME . "\n";
    echo "  Utilisateur: " . DB_USER . "\n";
}
?>
