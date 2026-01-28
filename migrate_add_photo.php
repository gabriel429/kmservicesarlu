<?php
/**
 * Script de migration - Ajouter colonne photo à la table users
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/MySQL.php';

try {
    // Vérifier si la colonne photo existe
    $result = MySQLCore::fetch(
        "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'photo' AND TABLE_SCHEMA = ?",
        ['u424760992_kmservices']
    );
    
    if (!$result) {
        // Ajouter la colonne photo
        MySQLCore::execute(
            "ALTER TABLE users ADD COLUMN photo VARCHAR(255) NULL AFTER prenom"
        );
        echo json_encode(['success' => true, 'message' => 'Colonne photo ajoutée avec succès']);
    } else {
        echo json_encode(['success' => true, 'message' => 'La colonne photo existe déjà']);
    }
    
    // Créer le dossier users s'il n'existe pas
    $uploadDir = __DIR__ . '/public/uploads/users/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
