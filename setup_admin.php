<?php
require_once 'config/config.php';
require_once 'app/Database.php';

use App\Database;

$db = Database::getInstance();

try {
    // Vérifier les utilisateurs existants
    $existingUsers = $db->fetchAll('SELECT id FROM users');
    
    if (!empty($existingUsers)) {
        echo "Des utilisateurs existent déjà. Opération annulée.";
        exit;
    }
    
    // Créer un utilisateur admin avec mot de passe par défaut
    $username = 'admin';
    $email = 'admin@kmservices.com';
    $password = 'admin123'; // À changer!
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $role = 'admin';
    
    $db->execute(
        "INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())",
        [$username, $email, $hashedPassword, $role]
    );
    
    echo "✅ Utilisateur administrateur créé avec succès!\n\n";
    echo "Identifiants de connexion:\n";
    echo "- Nom d'utilisateur: " . $username . "\n";
    echo "- Mot de passe: " . $password . "\n\n";
    echo "⚠️  IMPORTANT: Changez ce mot de passe dès la première connexion!\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
}
?>
