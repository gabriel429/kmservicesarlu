<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/MySQL.php';

header('Content-Type: text/plain; charset=utf-8');

if (!defined('APP_ENV') || APP_ENV !== 'development') {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

try {
    // Vérifier si le produit existe déjà
    $existing = MySQLCore::fetch("SELECT id FROM products WHERE slug = ?", ['produit-test']);
    if ($existing) {
        echo "Produit de test déjà présent (ID: {$existing['id']}).\n";
        exit;
    }

    $ok = MySQLCore::execute(
        "INSERT INTO products (nom, slug, description, prix, stock, actif) VALUES (?, ?, ?, ?, ?, 1)",
        ['Produit Test', 'produit-test', 'Produit de test sans image', 100.00, 5]
    );

    if ($ok) {
        $id = MySQLCore::lastInsertId();
        echo "✅ Produit de test créé. ID = {$id}\n";
    } else {
        echo "❌ Insertion non effectuée\n";
    }
} catch (Exception $e) {
    http_response_code(500);
    echo "Erreur: " . $e->getMessage() . "\n";
}
