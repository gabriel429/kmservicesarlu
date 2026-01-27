<?php
header('Content-Type: application/json; charset=UTF-8');
// Activer les erreurs pour le débogage des requêtes AJAX
ini_set('display_errors', 0); // On ne veut pas polluer le JSON
error_reporting(E_ALL);

session_start();

$response = ['success' => false, 'message' => 'Erreur'];

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/MySQL.php';

function ensureProductAuditTable() {
    try {
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
    } catch (Throwable $e) {}
}

function ensureProductImagesTable() {
    try {
        MySQLCore::execute(
            "CREATE TABLE IF NOT EXISTS product_images (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                image_path VARCHAR(255) NOT NULL,
                alt_text VARCHAR(255) NULL,
                ordre INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    } catch (Throwable $e) {}
}

function logProductAction($action, $productId = null, $details = null) {
    ensureProductAuditTable();
    $adminId = $_SESSION['admin_user_id'] ?? null;
    try {
        MySQLCore::execute(
            "INSERT INTO product_audit (admin_user_id, product_id, action, details) VALUES (?, ?, ?, ?)",
            [$adminId, $productId, $action, $details]
        );
    } catch (Throwable $e) {}
}
try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    ensureProductImagesTable();
    
    // Dossier uploads (fallback local en dev)
    $uploadsDir = dirname(dirname(__FILE__)) . '/uploads/products/';
    if (!is_dir($uploadsDir)) {
        @mkdir($uploadsDir, 0755, true);
    }
    
    function handleProductImageUploads($productId, $keepExisting = false) {
        global $uploadsDir;
        
        // Supprimer les anciennes images si on fait une mise à jour (sauf si keepExisting)
        if ($keepExisting === false) {
            $existingImages = MySQLCore::fetchAll("SELECT id, image_path FROM product_images WHERE product_id = ?", [$productId]);
            foreach ($existingImages as $img) {
                // Supprimer local si existant
                if (!str_starts_with((string)$img['image_path'], 'http') && file_exists($uploadsDir . $img['image_path'])) {
                    @unlink($uploadsDir . $img['image_path']);
                }
                MySQLCore::execute("DELETE FROM product_images WHERE id = ?", [$img['id']]);
            }
        }
        
        if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
            return; // Aucune image
        }
        
        $files = $_FILES['images'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $uploaded = 0;
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            if ($uploaded >= 5) break; // Max 5 images
            
            if (!in_array($files['type'][$i], $allowed)) continue;
            if ($files['size'][$i] > 5 * 1024 * 1024) continue; // 5MB max
            
            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            $ext = $ext === 'jpeg' ? 'jpg' : $ext;
            $filename = 'product_' . $productId . '_' . time() . '_' . $uploaded . '.' . $ext;

            if (move_uploaded_file($files['tmp_name'][$i], $uploadsDir . $filename)) {
                MySQLCore::execute(
                    "INSERT INTO product_images (product_id, image_path, ordre) VALUES (?, ?, ?)",
                    [$productId, $filename, $uploaded]
                );
                $uploaded++;
            }
        }
    }
    
    function handleImageUpload($productId, $existingImage = null) {
        global $uploadsDir;
        
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
            if (!str_starts_with((string)$existingImage, 'http') && file_exists($uploadsDir . $existingImage)) {
                @unlink($uploadsDir . $existingImage);
            }
        }
        
        // Générer un nom unique
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $ext = $ext === 'jpeg' ? 'jpg' : $ext;
        $filename = 'product_' . $productId . '_' . time() . '.' . $ext;

        // Fallback local
        if (!move_uploaded_file($file['tmp_name'], $uploadsDir . $filename)) {
            throw new Exception('Impossible de sauvegarder l\'image');
        }
        return $filename; // stocker le nom de fichier local
    }
    
    switch ($action) {
        case 'create':
            if (!isset($_SESSION['admin_role'])) {
                throw new Exception('Session non autorisée');
            }
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $prix = floatval($_POST['prix'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $actif = (isset($_POST['actif']) && ($_POST['actif'] === '1' || $_POST['actif'] === 1)) ? 1 : 0;
            
            if (empty($nom)) throw new Exception('Le nom du produit est requis');
            
            // Slugification plus robuste
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nom), '-'));
            $check = MySQLCore::fetch("SELECT id FROM products WHERE slug = ?", [$slug]);
            if ($check) { $slug .= '-' . time(); }
            
            $ok = MySQLCore::execute(
                "INSERT INTO products (nom, slug, description, prix, stock, actif) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$nom, $slug, $description, $prix, $stock, $actif]
            );
            
            if (!$ok) throw new Exception('Échec de l\'insertion du produit');
            
            $productId = MySQLCore::lastInsertId();
            
            // Gérer l'image principale si présente
            $image = null;
            if (isset($_FILES['image'])) {
                $image = handleImageUpload($productId);
                MySQLCore::execute(
                    "UPDATE products SET image_principale = ? WHERE id = ?",
                    [$image, $productId]
                );
            }
            
            // Gérer les images supplémentaires
            if (isset($_FILES['images'])) {
                handleProductImageUploads($productId, false);
            }
            
            $response = [
                'success' => true,
                'message' => 'Produit créé avec succès',
                'id' => $productId
            ];
            logProductAction('create_product', $productId, json_encode(['nom' => $nom, 'prix' => $prix, 'stock' => $stock]));
            break;
            
        case 'update':
            if (!isset($_SESSION['admin_role'])) {
                throw new Exception('Session expirée');
            }
            $id = intval($_POST['id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $prix = floatval($_POST['prix'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $actif = (isset($_POST['actif']) && ($_POST['actif'] === '1' || $_POST['actif'] === 1)) ? 1 : 0;
            
            if (!$id) throw new Exception('ID du produit requis');
            if (empty($nom)) throw new Exception('Le nom du produit est requis');
            
            // Récupérer l'image actuelle
            $current = MySQLCore::fetch("SELECT image_principale FROM products WHERE id = ?", [$id]);
            $currentImage = $current['image_principale'] ?? null;
            
            // Gérer l'image principale si présente
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $currentImage = handleImageUpload($id, $currentImage);
            }
            
            // Gérer les images supplémentaires
            if (isset($_FILES['images'])) {
                handleProductImageUploads($id, false);
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
            
            // Récupérer l'image avant suppression et la supprimer locales
            $product = MySQLCore::fetch("SELECT image_principale FROM products WHERE id = ?", [$id]);
            if ($product && !empty($product['image_principale'])) {
                $img = $product['image_principale'];
                if (!str_starts_with((string)$img, 'http') && file_exists($uploadsDir . $img)) {
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
    $response = [
        'success' => false, 
        'message' => $e->getMessage(),
        'debug' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]
    ];
}

echo json_encode($response);
exit;
