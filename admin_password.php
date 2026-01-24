#!/usr/bin/env php
<?php
/**
 * Script pour gérer les mots de passe administrateur
 * Utilisation: php admin_password.php
 */

define('BASE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
require_once BASE_PATH . 'config/config.php';
require_once BASE_PATH . 'app/Database.php';

use App\Database;

$db = Database::getInstance();

// Afficher le menu
echo "\n╔════════════════════════════════════════╗\n";
echo "║   Gestion des Mots de Passe Admin      ║\n";
echo "╚════════════════════════════════════════╝\n\n";

echo "Que voulez-vous faire?\n";
echo "1. Changer le mot de passe d'un utilisateur\n";
echo "2. Réinitialiser tous les mots de passe (DANGEREUX!)\n";
echo "3. Quitter\n\n";

$choice = trim(fgets(STDIN));

switch($choice) {
    case '1':
        changePassword();
        break;
    case '2':
        resetAllPasswords();
        break;
    case '3':
        echo "Au revoir!\n\n";
        exit;
    default:
        echo "Choix invalide!\n";
        break;
}

function changePassword() {
    global $db;
    
    echo "\n=== Changer le Mot de Passe ===\n";
    
    // Lister les utilisateurs
    echo "\nUtilisateurs existants:\n";
    $users = $db->fetchAll("SELECT id, username, email FROM users");
    
    if (empty($users)) {
        echo "Aucun utilisateur trouvé!\n";
        return;
    }
    
    foreach ($users as $index => $user) {
        echo ($index + 1) . ". {$user['username']} ({$user['email']})\n";
    }
    
    echo "\nSélectionnez un numéro: ";
    $userChoice = (int)trim(fgets(STDIN)) - 1;
    
    if (!isset($users[$userChoice])) {
        echo "Choix invalide!\n";
        return;
    }
    
    $user = $users[$userChoice];
    
    echo "Nouveau mot de passe: ";
    system('stty -echo');  // Masquer la saisie
    $password = trim(fgets(STDIN));
    system('stty echo');   // Afficher à nouveau
    echo "\n";
    
    echo "Confirmez le mot de passe: ";
    system('stty -echo');
    $passwordConfirm = trim(fgets(STDIN));
    system('stty echo');
    echo "\n";
    
    if ($password !== $passwordConfirm) {
        echo "❌ Les mots de passe ne correspondent pas!\n";
        return;
    }
    
    if (strlen($password) < 6) {
        echo "❌ Le mot de passe doit contenir au moins 6 caractères!\n";
        return;
    }
    
    // Générer le hash
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Mettre à jour
    try {
        $db->execute(
            "UPDATE users SET password = ? WHERE id = ?",
            [$hashedPassword, $user['id']]
        );
        echo "✅ Mot de passe changé pour {$user['username']}!\n\n";
    } catch (Exception $e) {
        echo "❌ Erreur: {$e->getMessage()}\n";
    }
}

function resetAllPasswords() {
    global $db;
    
    echo "\n⚠️  ATTENTION! Cette action réinitialisera TOUS les mots de passe!\n";
    echo "Les utilisateurs recevront les mots de passe par défaut.\n";
    echo "Êtes-vous sûr? (oui/non): ";
    
    $confirm = strtolower(trim(fgets(STDIN)));
    
    if ($confirm !== 'oui') {
        echo "Opération annulée.\n";
        return;
    }
    
    try {
        $defaultPassword = password_hash('password', PASSWORD_BCRYPT);
        
        $db->execute(
            "UPDATE users SET password = ?",
            [$defaultPassword]
        );
        
        echo "✅ Tous les mots de passe ont été réinitialisés à 'password'!\n";
        echo "⚠️  Demandez aux utilisateurs de changer leurs mots de passe au première connexion.\n\n";
    } catch (Exception $e) {
        echo "❌ Erreur: {$e->getMessage()}\n";
    }
}

?>
