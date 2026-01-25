<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/MySQL.php';

echo "<h1>Test Insertion Projet</h1>";

try {
    $titre = "Projet Test Web " . time();
    $slug = "projet-test-web-" . time();
    $desc = "Description de test";
    $loc = "Douala";
    $statut = "en_cours";
    $actif = 1;

    $sql = "INSERT INTO projects (titre, slug, description, localisation, statut, actif) VALUES (?, ?, ?, ?, ?, ?)";
    $params = [$titre, $slug, $desc, $loc, $statut, $actif];
    
    echo "Exécution de la requête...<br>";
    $ok = MySQLCore::execute($sql, $params);
    
    if ($ok) {
        $id = MySQLCore::lastInsertId();
        echo "✅ Succès ! ID inséré : " . $id . "<br>";
        
        $check = MySQLCore::fetch("SELECT * FROM projects WHERE id = ?", [$id]);
        echo "Vérification en base : <pre>";
        print_r($check);
        echo "</pre>";
    } else {
        echo "❌ Échec de l'insertion (MySQLCore::execute a retourné false)<br>";
    }
} catch (Exception $e) {
    echo "💥 Erreur : " . $e->getMessage() . "<br>";
    echo "Fichier : " . $e->getFile() . " à la ligne " . $e->getLine() . "<br>";
}
