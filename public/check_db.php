<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/MySQL.php';

try {
    $db = \App\Database::getInstance();
    $dbName = MySQLCore::fetch("SELECT DATABASE() as db")['db'];
    $projectsCount = MySQLCore::fetch("SELECT COUNT(*) as c FROM projects")['c'];
    
    echo "<h1>Debug Connexion DB</h1>";
    echo "DB_NAME (config) : " . DB_NAME . "<br>";
    echo "Base connectée : " . $dbName . "<br>";
    echo "Nombre de projets : " . $projectsCount . "<br>";
    
    if ($projectsCount > 0) {
        $last = MySQLCore::fetch("SELECT * FROM projects ORDER BY id DESC LIMIT 1");
        echo "Dernier projet : " . $last['titre'] . " (ID: " . $last['id'] . ")<br>";
    }
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
