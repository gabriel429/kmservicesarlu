<?php namespace App;
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

        $viewPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $uri . '.php';
        $viewPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $viewPath);
        
        if (file_exists($viewPath)) return $viewPath;
        
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . '404.php';
    }
}
