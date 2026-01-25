<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/MySQL.php';

$projects = MySQLCore::fetchAll("SELECT * FROM projects");
echo "<h2>Liste des projets en base</h2>";
echo "Nombre : " . count($projects) . "<br><hr>";
foreach ($projects as $p) {
    echo "ID: " . $p['id'] . " | Titre: " . $p['titre'] . " | Statut: " . $p['statut'] . "<br>";
}
