<?php namespace App;

// Alias pour les classes sans namespace
if (!class_exists('MySQLCore')) {
    require_once __DIR__ . '/MySQL.php';
}

class Router {
    private $routes = [];
    public function add($path, $view) {
        $this->routes[$path] = $view;
    }
    public function dispatch($uri) {
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // On cherche à savoir quel morceau de l'URL est la page
        // On compare l'URI avec le chemin du script actuel
        $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']); // /kmservices/public/index.php
        $scriptDir = str_replace('\\', '/', dirname($scriptPath));     // /kmservices/public
        $baseDir = str_replace('/public', '', $scriptDir);              // /kmservices

        // Enlever le dossier de base de l'URL
        if ($baseDir !== '/' && strpos($uri, $baseDir) === 0) {
            $uri = substr($uri, strlen($baseDir));
        }

        // Enlever /public de l'URL si présent
        if (strpos($uri, '/public') === 0) {
            $uri = substr($uri, 7);
        }

        $uri = trim($uri, '/');
        if ($uri === '' || $uri === 'index.php') $uri = 'accueil';

        // ... Gestion Admin
        if (strpos($uri, 'admin') === 0) {
            $parts = explode('/', $uri);
            $page = isset($parts[1]) ? $parts[1] : 'dashboard';
            $viewPath = __DIR__ . '/../views/admin/' . $page . '.php';
            if (file_exists($viewPath)) return $viewPath;
        }

        // Gestion des routes dynamiques pour les produits
        if (strpos($uri, 'produit/') === 0) {
            $slug = substr($uri, 8);
            $slug = explode('?', $slug)[0]; // Enlever query string
            
            // Charger le produit depuis la base de données
            if (!class_exists('MySQLCore')) {
                require_once __DIR__ . '/MySQL.php';
            }
            
            try {
                $product = \MySQLCore::fetch(
                    "SELECT id, nom, slug, description, prix, prix_promotion, stock, reference, image_principale, category_id 
                     FROM products WHERE slug = ? AND actif = 1",
                    [$slug]
                );
                
                if ($product) {
                    // Charger aussi les produits similaires
                    $products = \MySQLCore::fetchAll(
                        "SELECT id, nom, slug, description, prix, stock, image_principale 
                         FROM products WHERE actif = 1 ORDER BY ordre ASC, created_at DESC LIMIT 10"
                    );
                    
                    // Stocker les données dans les variables globales
                    $_GET['_product'] = $product;
                    $_GET['_products'] = $products;
                    
                    return __DIR__ . '/../views/product-detail.php';
                }
            } catch (Exception $e) {
                // En cas d'erreur, afficher 404
                return __DIR__ . '/../views/404.php';
            }
        }

        // Gestion des routes dynamiques pour les projets
        if (strpos($uri, 'projets/') === 0) {
            $slug = substr($uri, 8);
            $slug = explode('?', $slug)[0]; // Enlever query string
            
            // Charger le projet depuis la base de données
            if (!class_exists('MySQLCore')) {
                require_once __DIR__ . '/MySQL.php';
            }
            
            try {
                $project = \MySQLCore::fetch(
                    "SELECT id, titre, slug, description, localisation, image_principale, video_url, date_fin, statut 
                     FROM projects WHERE slug = ?",
                    [$slug]
                );
                
                if ($project) {
                    // Charger aussi les projets similaires
                    $projects = \MySQLCore::fetchAll(
                        "SELECT id, titre, slug, description, localisation, image_principale, statut 
                         FROM projects ORDER BY ordre ASC, created_at DESC LIMIT 10"
                    );
                    
                    // Stocker les données dans les variables globales
                    $_GET['_project'] = $project;
                    $_GET['_projects'] = $projects;
                    
                    return __DIR__ . '/../views/project-detail.php';
                }
            } catch (Exception $e) {
                // En cas d'erreur, afficher 404
                return __DIR__ . '/../views/404.php';
            }
        }

        $viewPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $uri . '.php';
        $viewPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $viewPath);
        
        if (file_exists($viewPath)) return $viewPath;
        
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . '404.php';
    }
}
