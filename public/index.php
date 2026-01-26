<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/MySQL.php';
require_once __DIR__ . '/../app/Router.php';
require_once __DIR__ . '/../app/helpers.php';

use App\Router;
use App\Database;

$router = new Router();
$view = $router->dispatch($_SERVER['REQUEST_URI']);

// Valeurs par défaut pour le layout
$pageTitle = APP_NAME;

// Capture du contenu de la vue
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$projectRoot = str_replace('/public', '', $scriptDir);
$path = ltrim(substr($uri, strlen($projectRoot)), '/');

if (str_starts_with($path, 'admin') && !str_contains($path, 'login') && !str_contains($path, 'logout')) {
    // Mode Admin : On utilise le layout admin qui inclut lui-même la vue via $adminContentPath
    $adminContentPath = $view;
    include __DIR__ . '/../views/admin/layout.php';
} else {
    // Mode Front : On capture la vue et on l'injecte dans $content du layout front
    ob_start();
    
    // Passer les données des produits si disponibles
    if (isset($_GET['_product'])) {
        $product = $_GET['_product'];
    }
    if (isset($_GET['_products'])) {
        $products = $_GET['_products'];
    }
    
    if (file_exists($view)) {
        include $view;
    } else {
        include __DIR__ . '/../views/404.php';
    }
    $content = ob_get_clean();
    include __DIR__ . '/../views/layout.php';
}
