<?php
require_once '../config/config.php';
require_once '../app/Database.php';

use App\Database;

$db = Database::getInstance();

echo "=== Diagnostic de Connexion Admin ===\n\n";

// Récupérer l'utilisateur admin
$user = $db->fetch("SELECT id, username, email, password FROM users WHERE username = 'admin'");

if (!$user) {
    echo "❌ Utilisateur 'admin' non trouvé!\n";
    exit;
}

echo "✅ Utilisateur trouvé:\n";
echo "  - ID: {$user['id']}\n";
echo "  - Username: {$user['username']}\n";
echo "  - Email: {$user['email']}\n";
echo "  - Hash stocké: " . substr($user['password'], 0, 20) . "...\n\n";

// Test de vérification de mot de passe
$testPassword = 'admin123';
echo "Test de vérification avec le mot de passe 'admin123':\n";

if (password_verify($testPassword, $user['password'])) {
    echo "✅ Le mot de passe est CORRECT!\n";
} else {
    echo "❌ Le mot de passe est INCORRECT!\n";
    echo "\nTentative de réinitialiser le mot de passe...\n";
    
    $newPassword = 'admin123';
    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
    
    $db->execute(
        "UPDATE users SET password = ? WHERE id = ?",
        [$newHash, $user['id']]
    );
    
    echo "✅ Mot de passe réinitialisé à 'admin123'\n";
}
?>
