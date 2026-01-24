<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(0);
ini_set('display_errors', 0);
session_start();

$response = ['success' => false, 'message' => 'Erreur'];

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/MySQL.php';
require_once __DIR__ . '/../../app/Supabase.php';

function ensureProductAuditTable() {
    MySQLCore::execute(
        "CREATE TABLE IF NOT EXISTS product_audit (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_user_id INT NULL,
            product_id INT NULL,
            action VARCHAR(50) NOT NULL,
            details TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function logProductAction($action, $productId = null, $details = null) {
    ensureProductAuditTable();
    $adminId = $_SESSION['admin_user_id'] ?? null;
    MySQLCore::execute(
        "INSERT INTO product_audit (admin_user_id, product_id, action, details) VALUES (?, ?, ?, ?)",
        [$adminId, $productId, $action, $details]
    );
}
try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    // Dossier uploads (fallback local en dev)
    $uploadsDir = dirname(dirname(__FILE__)) . '/uploads/products/';
    if (!is_dir($uploadsDir)) {
        @mkdir($uploadsDir, 0755, true);
    }
    
    function handleImageUpload($productId, $existingImage = null) {
        global $uploadsDir;
        $supabase = \App\SupabaseStorage::fromEnv('uploads');
        
        if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return $existingImage; // Aucun fichier uploadé
        }
        
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Erreur lors de l\'upload: ' . $_FILES['image']['error']);
        }
        
        $file = $_FILES['image'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowed)) {
            throw new Exception('Type de fichier non autorisé. JPG, PNG, GIF acceptés.');
        }
        
        if ($file['size'] > 5 * 1024 * 1024) { // 5MB
            throw new Exception('Fichier trop volumineux (max 5MB)');
        }
        
        // Supprimer l'ancienne image si elle existe
        if ($existingImage) {
            if ($supabase && is_string($existingImage) && str_starts_with($existingImage, 'http')) {
                $supabase->deleteByPublicUrl($existingImage);
            } elseif (!str_starts_with((string)$existingImage, 'http') && file_exists($uploadsDir . $existingImage)) {
                @unlink($uploadsDir . $existingImage);
            }
        }
        
        // Générer un nom unique
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $ext = $ext === 'jpeg' ? 'jpg' : $ext;
        $filename = 'product_' . $productId . '_' . time() . '.' . $ext;

        if ($supabase) {
            $storagePath = 'products/' . $filename;
            $publicUrl = $supabase->uploadFile($file['tmp_name'], $storagePath, $file['type'] ?: 'image/jpeg');
            if (!$publicUrl) {
                throw new Exception('Échec de l\'upload vers Supabase Storage');
            }
            return $publicUrl; // stocker l'URL publique
        } else {
            // Fallback local
            if (!move_uploaded_file($file['tmp_name'], $uploadsDir . $filename)) {
                throw new Exception('Impossible de sauvegarder l\'image');
            }
            return $filename; // stocker le nom de fichier local
        }
    }
    
    switch ($action) {
        case 'create':
            if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
                throw new Exception('Accès refusé: réservé aux administrateurs');
            }
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $prix = floatval($_POST['prix'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $actif = isset($_POST['actif']) ? 1 : 0;
            
            if (empty($nom)) throw new Exception('Le nom du produit est requis');
            
            $slug = strtolower(str_replace(' ', '-', $nom));
            
            MySQLCore::execute(
                "INSERT INTO products (nom, slug, description, prix, stock, actif) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$nom, $slug, $description, $prix, $stock, $actif]
            );
            
            $productId = MySQLCore::lastInsertId();
            
            // Gérer l'image si présente
            $image = null;
            if (isset($_FILES['image'])) {
                $image = handleImageUpload($productId);
                MySQLCore::execute(
                    "UPDATE products SET image_principale = ? WHERE id = ?",
                    [$image, $productId]
                );
            }
            
            $response = [
                'success' => true,
                'message' => 'Produit créé avec succès',
                'id' => $productId
            ];
            logProductAction('create_product', $productId, json_encode(['nom' => $nom, 'prix' => $prix, 'stock' => $stock]));
            break;
            
        case 'update':
            if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
                throw new Exception('Accès refusé: réservé aux administrateurs');
            }
            $id = intval($_POST['id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $prix = floatval($_POST['prix'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $actif = isset($_POST['actif']) ? 1 : 0;
            
            if (!$id) throw new Exception('ID du produit requis');
            if (empty($nom)) throw new Exception('Le nom du produit est requis');
            
            // Récupérer l'image actuelle
            $current = MySQLCore::fetch("SELECT image_principale FROM products WHERE id = ?", [$id]);
            $currentImage = $current['image_principale'] ?? null;
            
            // Gérer l'image si présente
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $currentImage = handleImageUpload($id, $currentImage);
            }
            
            MySQLCore::execute(
                "UPDATE products SET nom = ?, description = ?, prix = ?, stock = ?, actif = ?, image_principale = ? 
                 WHERE id = ?",
                [$nom, $description, $prix, $stock, $actif, $currentImage, $id]
            );
            
            $response = ['success' => true, 'message' => 'Produit mis à jour avec succès'];
            logProductAction('update_product', $id, json_encode(['nom' => $nom, 'prix' => $prix, 'stock' => $stock, 'actif' => $actif]));
            break;
            
        case 'delete':
            if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
                throw new Exception('Accès refusé: réservé aux administrateurs');
            }
            $id = intval($_POST['id'] ?? 0);
            if (!$id) throw new Exception('ID du produit requis');
            
            // Récupérer l'image avant suppression et la supprimer (local ou Supabase)
            $product = MySQLCore::fetch("SELECT image_principale FROM products WHERE id = ?", [$id]);
            if ($product && $product['image_principale']) {
                $img = $product['image_principale'];
                $supabase = \App\SupabaseStorage::fromEnv('uploads');
                if ($supabase && is_string($img) && str_starts_with($img, 'http')) {
                    $supabase->deleteByPublicUrl($img);
                } elseif (!str_starts_with((string)$img, 'http') && file_exists($uploadsDir . $img)) {
                    @unlink($uploadsDir . $img);
                }
            }
            
            MySQLCore::execute("DELETE FROM products WHERE id = ?", [$id]);
            $response = ['success' => true, 'message' => 'Produit supprimé avec succès'];
            logProductAction('delete_product', $id, null);
            break;
            
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            if (!$id) throw new Exception('ID du produit requis');
            
            $product = MySQLCore::fetch(
                "SELECT id, nom, description, prix, stock, actif, image_principale FROM products WHERE id = ?",
                [$id]
            );
            
            if (!$product) throw new Exception('Produit non trouvé');
            
            $response = ['success' => true, 'data' => $product];
            break;
            
        default:
            throw new Exception('Action invalide: ' . $action);
    }
    
} catch (Throwable $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
exit;
