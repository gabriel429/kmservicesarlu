<?php
/**
 * Routeur simple pour KM Services
 */

namespace App;

class Router {
    private $routes = [];
    private $method;
    private $uri;

    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Supprimer la barre oblique au début si elle existe
        $this->uri = ltrim($this->uri, '/');
        
        if (empty($this->uri)) {
            $this->uri = 'accueil';
        }
    }

    public function get($route, $controller) {
        $this->addRoute('GET', $route, $controller);
    }

    public function post($route, $controller) {
        $this->addRoute('POST', $route, $controller);
    }

    private function addRoute($method, $route, $controller) {
        $this->routes[$method][$route] = $controller;
    }

    public function dispatch() {
        $method = $this->method;
        
        // Chercher une correspondance exacte
        if (isset($this->routes[$method][$this->uri])) {
            return $this->routes[$method][$this->uri];
        }

        // Chercher une route paramétrisée
        foreach ($this->routes[$method] ?? [] as $route => $controller) {
            if ($this->matchRoute($route, $this->uri)) {
                return $controller;
            }
        }

        return 'accueil';
    }

    private function matchRoute($route, $uri) {
        $route = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', '([0-9]+)', $route);
        $route = '/^' . str_replace('/', '\/', $route) . '$/';
        return preg_match($route, $uri);
    }

    public function getUri() {
        return $this->uri;
    }

    public function getMethod() {
        return $this->method;
    }
}
?>
