<?php
header('Content-Type: application/json; charset=UTF-8');
// Activer les erreurs pour le débogage des requêtes AJAX
ini_set('display_errors', 0); // On ne veut pas polluer le JSON
error_reporting(E_ALL);

session_start();

$response = ['success' => false, 'message' => 'Erreur'];

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/MySQL.php';

function ensureProjectAuditTable() {
    try {
        MySQLCore::execute(
            "CREATE TABLE IF NOT EXISTS project_audit (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_user_id INT NULL,
                project_id INT NULL,
                action VARCHAR(50) NOT NULL,
                details TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    } catch (Throwable $e) {}
}

function ensureProjectImagesTable() {
    try {
        MySQLCore::execute(
            "CREATE TABLE IF NOT EXISTS project_images (
                id INT AUTO_INCREMENT PRIMARY KEY,
                project_id INT NOT NULL,
                image_path VARCHAR(255) NOT NULL,
                alt_text VARCHAR(255) NULL,
                ordre INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    } catch (Throwable $e) {}
}

function logProjectAction($action, $projectId = null, $details = null) {
    ensureProjectAuditTable();
    $adminId = $_SESSION['admin_user_id'] ?? null;
    try {
        MySQLCore::execute(
            "INSERT INTO project_audit (admin_user_id, project_id, action, details) VALUES (?, ?, ?, ?)",
            [$adminId, $projectId, $action, $details]
        );
    } catch (Throwable $e) {}
}

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    ensureProjectImagesTable();
    
    // Dossier uploads (fallback local en dev)
    $uploadsDir = dirname(dirname(__FILE__)) . '/uploads/projects/';
    if (!is_dir($uploadsDir)) {
        @mkdir($uploadsDir, 0755, true);
    }
    
    function handleProjectImageUploads($projectId, $keepExisting = false) {
        global $uploadsDir;
        
        // Supprimer les anciennes images si on fait une mise à jour (sauf si keepExisting)
        if ($keepExisting === false) {
            $existingImages = MySQLCore::fetchAll("SELECT id, image_path FROM project_images WHERE project_id = ?", [$projectId]);
            foreach ($existingImages as $img) {
                // Supprimer local si existant
                if (!str_starts_with((string)$img['image_path'], 'http') && file_exists($uploadsDir . $img['image_path'])) {
                    @unlink($uploadsDir . $img['image_path']);
                }
                MySQLCore::execute("DELETE FROM project_images WHERE id = ?", [$img['id']]);
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
            $filename = 'project_' . $projectId . '_' . time() . '_' . $uploaded . '.' . $ext;

            if (move_uploaded_file($files['tmp_name'][$i], $uploadsDir . $filename)) {
                MySQLCore::execute(
                    "INSERT INTO project_images (project_id, image_path, ordre) VALUES (?, ?, ?)",
                    [$projectId, $filename, $uploaded]
                );
                $uploaded++;
            }
        }
    }
    
    switch ($action) {
        case 'create':
            if (!isset($_SESSION['admin_role'])) {
                throw new Exception('Session expirée ou non autorisée');
            }
            $titre = trim($_POST['titre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $localisation = trim($_POST['localisation'] ?? '');
            $statut = $_POST['statut'] ?? 'en_cours';
            $actif = (isset($_POST['actif']) && ($_POST['actif'] === '1' || $_POST['actif'] === 1)) ? 1 : 0;
            
            if (empty($titre)) throw new Exception('Le titre du projet est requis');
            
            // Slugification simple mais plus robuste
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titre), '-'));
            
            // Vérifier si le slug existe déjà (pour ne pas bloquer l'insert)
            $check = MySQLCore::fetch("SELECT id FROM projects WHERE slug = ?", [$slug]);
            if ($check) {
                $slug .= '-' . time();
            }
            
            $ok = MySQLCore::execute(
                "INSERT INTO projects (titre, slug, description, localisation, statut, actif) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$titre, $slug, $description, $localisation, $statut, $actif]
            );
            
            if (!$ok) throw new Exception('Échec de l\'insertion en base de données');
            
            $projectId = MySQLCore::lastInsertId();
            
            // Gérer les images
            if (isset($_FILES['images'])) {
                handleProjectImageUploads($projectId, false);
                // Définir l'image principale avec la première image
                $firstImg = MySQLCore::fetch("SELECT image_path FROM project_images WHERE project_id = ? ORDER BY ordre ASC, id ASC LIMIT 1", [$projectId]);
                if ($firstImg && isset($firstImg['image_path'])) {
                    MySQLCore::execute("UPDATE projects SET image_principale = ? WHERE id = ?", [$firstImg['image_path'], $projectId]);
                }
            }
            
            $response = [
                'success' => true,
                'message' => 'Projet créé avec succès',
                'id' => $projectId
            ];
            logProjectAction('create_project', $projectId, json_encode(['titre' => $titre, 'localisation' => $localisation, 'statut' => $statut]));
            break;
            
        case 'update':
            if (!isset($_SESSION['admin_role'])) {
                throw new Exception('Session expirée');
            }
            $id = intval($_POST['id'] ?? 0);
            $titre = trim($_POST['titre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $localisation = trim($_POST['localisation'] ?? '');
            $statut = $_POST['statut'] ?? 'en_cours';
            $actif = (isset($_POST['actif']) && ($_POST['actif'] === '1' || $_POST['actif'] === 1)) ? 1 : 0;
            
            if (!$id) throw new Exception('ID du projet requis');
            if (empty($titre)) throw new Exception('Le titre du projet est requis');
            
            MySQLCore::execute(
                "UPDATE projects SET titre = ?, description = ?, localisation = ?, statut = ?, actif = ? 
                 WHERE id = ?",
                [$titre, $description, $localisation, $statut, $actif, $id]
            );
            
            // Gérer les images si présentes
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                handleProjectImageUploads($id, false);
                // Mettre à jour l'image principale
                $firstImg = MySQLCore::fetch("SELECT image_path FROM project_images WHERE project_id = ? ORDER BY ordre ASC, id ASC LIMIT 1", [$id]);
                if ($firstImg && isset($firstImg['image_path'])) {
                    MySQLCore::execute("UPDATE projects SET image_principale = ? WHERE id = ?", [$firstImg['image_path'], $id]);
                }
            }
            
            $response = ['success' => true, 'message' => 'Projet mis à jour avec succès'];
            logProjectAction('update_project', $id, json_encode(['titre' => $titre, 'localisation' => $localisation, 'statut' => $statut, 'actif' => $actif]));
            break;
            
        case 'delete':
            if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
                throw new Exception('Accès refusé: réservé aux administrateurs');
            }
            $id = intval($_POST['id'] ?? 0);
            if (!$id) throw new Exception('ID du projet requis');
            
            // Supprimer les images
            $images = MySQLCore::fetchAll("SELECT id, image_path FROM project_images WHERE project_id = ?", [$id]);
            foreach ($images as $img) {
                if (file_exists($uploadsDir . $img['image_path'])) {
                    @unlink($uploadsDir . $img['image_path']);
                }
            }
            
            MySQLCore::execute("DELETE FROM project_images WHERE project_id = ?", [$id]);
            MySQLCore::execute("DELETE FROM projects WHERE id = ?", [$id]);
            $response = ['success' => true, 'message' => 'Projet supprimé avec succès'];
            logProjectAction('delete_project', $id, null);
            break;
            
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            if (!$id) throw new Exception('ID du projet requis');
            
            $project = MySQLCore::fetch(
                "SELECT id, titre, description, localisation, statut, actif FROM projects WHERE id = ?",
                [$id]
            );
            
            if (!$project) throw new Exception('Projet non trouvé');
            
            $response = ['success' => true, 'data' => $project];
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
