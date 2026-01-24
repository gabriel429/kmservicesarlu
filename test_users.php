<?php
require_once 'config/config.php';
require_once 'app/Database.php';

use App\Database;

$db = Database::getInstance();

try {
    echo "=== Test de la Table Users ===\n\n";
    
    // Essayer de récupérer les utilisateurs
    $users = $db->fetchAll('SELECT id, username, email, role FROM users');
    
    if (empty($users)) {
        echo "❌ Aucun utilisateur trouvé dans la base de données!\n";
    } else {
        echo "✅ Utilisateurs trouvés:\n";
        foreach ($users as $user) {
            echo "  - ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Role: {$user['role']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>
