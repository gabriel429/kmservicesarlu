<?php
/**
 * Script d'initialisation des colonnes manquantes
 * Ajoute automatiquement les colonnes photo et prenom si elles manquent
 */

require_once __DIR__ . '/MySQL.php';

try {
    // Ajouter colonne photo si elle n'existe pas
    try {
        MySQLCore::fetch("SELECT photo FROM users LIMIT 1");
    } catch (Exception $e) {
        // La colonne photo n'existe pas, l'ajouter
        MySQLCore::execute("ALTER TABLE users ADD COLUMN photo VARCHAR(255) NULL AFTER prenom");
        error_log("Colonne 'photo' ajoutée à la table users");
    }
    
    // Ajouter colonne prenom si elle n'existe pas
    try {
        MySQLCore::fetch("SELECT prenom FROM users LIMIT 1");
    } catch (Exception $e) {
        // La colonne prenom n'existe pas, l'ajouter
        MySQLCore::execute("ALTER TABLE users ADD COLUMN prenom VARCHAR(100) NULL AFTER nom");
        error_log("Colonne 'prenom' ajoutée à la table users");
    }
    
    // Créer le dossier uploads/users s'il n'existe pas
    $uploadDir = __DIR__ . '/public/uploads/users/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        error_log("Dossier uploads/users créé");
    }
    
} catch (Exception $e) {
    error_log("Erreur initialisation: " . $e->getMessage());
}
?>
