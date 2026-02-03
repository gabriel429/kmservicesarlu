<?php
// Configuration globale

define('DB_HOST', 'localhost');
define('DB_NAME', 'km_services');
define('DB_USER', 'root');
define('DB_PASS', '');
define('SITE_URL', 'https://kmservicesarlu.cd');

define('WHATSAPP_NUMBER', '243000000000'); // Remplacer par le numéro officiel

define('CSRF_TOKEN_KEY', 'km_csrf_token');

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
}
