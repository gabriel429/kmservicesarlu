<?php
/**
 * KM Services - Point d'entrée principal
 * Version 1.0
 */

// Démarrage de la session
session_start();

// Inclusion des fichiers de configuration
require_once '../config/config.php';

// Inclusion des classes principales
require_once BASE_PATH . 'app/Database.php';
require_once BASE_PATH . 'app/MySQL.php';
require_once BASE_PATH . 'app/Router.php';
require_once BASE_PATH . 'app/Supabase.php';

use App\Database;
use App\Router;

// Initialisation du routeur
$router = new Router();
$db = null; // Using MySQLCore (MySQLi) for queries

// Déterminer la page
$page = $router->getUri();
$method = $router->getMethod();

// Configuration du header pour UTF-8 (éviter pour la route image)
if ($page !== 'img') {
    header('Content-Type: text/html; charset=utf-8');
}

// Variables par défaut
$pageTitle = 'KM Services';
$contentPath = BASE_PATH . 'views/accueil.php';

// Récupérer les données communes
try {
    $projects = MySQLCore::fetchAll(
        "SELECT id, titre, slug, description, localisation, image_principale, statut 
         FROM projects WHERE actif = 1 ORDER BY ordre ASC, created_at DESC LIMIT 100"
    );
    
    $services = MySQLCore::fetchAll(
        "SELECT id, nom, description, icon FROM services WHERE actif = 1 ORDER BY ordre ASC"
    );
    
    $categories = MySQLCore::fetchAll(
        "SELECT id, nom, slug FROM product_categories WHERE actif = 1 ORDER BY nom ASC"
    );
    
    $products = MySQLCore::fetchAll(
        "SELECT id, nom, slug, description, prix, image_principale, stock 
         FROM products WHERE actif = 1 ORDER BY ordre ASC, created_at DESC LIMIT 50"
    );
    
} catch (Exception $e) {
    $projects = [];
    $services = [];
    $categories = [];
    $products = [];
}

// Traitement de la connexion admin
$loginError = '';
if ($page === 'admin/login' && $method === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        try {
            $user = MySQLCore::fetch(
                "SELECT id, username, password, role FROM users WHERE username = ? OR email = ?",
                [$username, $username]
            );
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_role'] = $user['role'];
                header('Location: ' . APP_URL . 'admin/dashboard');
                exit;
            } else {
                $loginError = 'Identifiant ou mot de passe incorrect.';
            }
        } catch (Exception $e) {
            $loginError = 'Erreur de connexion: ' . $e->getMessage();
        }
    } else {
        $loginError = 'Veuillez remplir tous les champs.';
    }
}

// Routage simple
switch($page) {
    case 'img':
        // Image resizing endpoint
        include __DIR__ . '/img.php';
        exit;

    case 'accueil':
    case '':
        $pageTitle = 'Accueil';
        $contentPath = BASE_PATH . 'views/accueil.php';
        break;
        
    case 'apropos':
        $pageTitle = 'À Propos';
        $contentPath = BASE_PATH . 'views/apropos.php';
        break;
        
    case 'services':
        $pageTitle = 'Services';
        $contentPath = BASE_PATH . 'views/services.php';
        break;
    
    case 'devis':
        $pageTitle = 'Demande de Devis';
        if ($method === 'POST') {
            $nom = $_POST['nom'] ?? '';
            $email = $_POST['email'] ?? '';
            $telephone = $_POST['telephone'] ?? '';
            $localisation = $_POST['localisation'] ?? '';
            $service = $_POST['service'] ?? 'general';
            $description = $_POST['description'] ?? '';
            $delai_souhaite = $_POST['delai_souhaite'] ?? '';
            $budget_estime = $_POST['budget_estime'] ?? '';
            $type_forage = $_POST['type_forage'] ?? '';
            $uploadedRelPath = null;
            $uploadedUrl = null;

            try {
                if (empty($nom) || empty($email) || empty($localisation)) {
                    throw new Exception('Veuillez remplir les champs requis');
                }

                // Gestion du document joint (optionnel)
                if (isset($_FILES['document_joint']) && is_array($_FILES['document_joint'])) {
                    $file = $_FILES['document_joint'];
                    if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                        if ($file['error'] !== UPLOAD_ERR_OK) {
                            throw new Exception('Téléversement du fichier échoué (code ' . (int)$file['error'] . ').');
                        }
                        // Taille max 5 Mo
                        if ($file['size'] > 5 * 1024 * 1024) {
                            throw new Exception('Le fichier dépasse la taille maximale autorisée de 5 Mo.');
                        }
                        // Validation MIME
                        $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
                        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : null;
                        if ($finfo) { finfo_close($finfo); }
                        $allowed = [
                            'image/jpeg' => 'jpg',
                            'image/pjpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/webp' => 'webp',
                            'application/pdf' => 'pdf',
                        ];
                        if ($mime && !isset($allowed[$mime])) {
                            throw new Exception('Type de fichier non supporté. Formats autorisés: PDF, JPG, PNG, WEBP.');
                        }
                        // Déterminer l'extension de sortie
                        $ext = $mime && isset($allowed[$mime]) ? $allowed[$mime] : null;
                        if (!$ext) {
                            // fallback basique via extension originale si MIME indisponible
                            $origExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            if (!in_array($origExt, ['jpg','jpeg','png','webp','pdf'])) {
                                throw new Exception('Extension de fichier non supportée.');
                            }
                            $ext = $origExt === 'jpeg' ? 'jpg' : $origExt;
                        }

                        // Nom de fichier unique
                        try {
                            $rand = bin2hex(random_bytes(4));
                        } catch (Throwable $e) {
                            $rand = uniqid();
                        }
                        $filename = 'devis_' . date('Ymd_His') . '_' . $rand . '.' . $ext;
                        
                        // Upload vers Supabase Storage si configuré, sinon fallback local
                        $supabase = \App\SupabaseStorage::fromEnv();
                        if ($supabase) {
                            $storagePath = 'devis/' . $filename;
                            $publicUrl = $supabase->uploadFile($file['tmp_name'], $storagePath, $mime);
                            if (!$publicUrl) {
                                throw new Exception('Échec de l’upload vers Supabase Storage.');
                            }
                            $uploadedUrl = $publicUrl;
                        } else {
                            // Fallback local (développement)
                            $uploadDir = BASE_PATH . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'devis';
                            if (!is_dir($uploadDir)) {
                                @mkdir($uploadDir, 0775, true);
                            }
                            if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
                                throw new Exception('Impossible de stocker le fichier (répertoire non accessible).');
                            }
                            $destPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
                            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                                throw new Exception('Échec lors de l’enregistrement du fichier sur le serveur.');
                            }
                            $uploadedRelPath = 'uploads/devis/' . $filename;
                        }
                    }
                }
                if ($service === 'forage') {
                    // Assurer la table drilling_requests existe (si nécessaire)
                    MySQLCore::execute("CREATE TABLE IF NOT EXISTS drilling_requests (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        nom VARCHAR(150) NOT NULL,
                        email VARCHAR(150) NOT NULL,
                        telephone VARCHAR(20) NOT NULL,
                        type_forage VARCHAR(100),
                        profondeur_estimee INT,
                        localisation VARCHAR(255) NOT NULL,
                        description LONGTEXT,
                        delai_souhaite VARCHAR(100),
                        budget_estime DECIMAL(12,2),
                        document_joint VARCHAR(255),
                        statut ENUM('nouveau','en_attente','contacte','complete','refuse') DEFAULT 'nouveau',
                        lu TINYINT DEFAULT 0,
                        notes TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        treated_by INT
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                    MySQLCore::execute(
                        "INSERT INTO drilling_requests (nom, email, telephone, type_forage, localisation, description, delai_souhaite, budget_estime, document_joint, statut) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'nouveau')",
                        [$nom, $email, $telephone, $type_forage, $localisation, $description, $delai_souhaite, $budget_estime, ($uploadedUrl ?: $uploadedRelPath)]
                    );
                } else {
                    // Enregistrer en message de contact avec sujet Devis - Service
                    MySQLCore::execute("CREATE TABLE IF NOT EXISTS contacts (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        nom VARCHAR(150) NOT NULL,
                        email VARCHAR(150) NOT NULL,
                        telephone VARCHAR(20),
                        sujet VARCHAR(200),
                        message LONGTEXT NOT NULL,
                        statut ENUM('nouveau','traite','archive') DEFAULT 'nouveau',
                        lu TINYINT DEFAULT 0,
                        response_email VARCHAR(1000),
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        treated_by INT
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                    $sujet = 'Devis - ' . ucfirst($service);
                    $msg = $description . "\n\nLocalisation: " . $localisation . "\nDélai souhaité: " . $delai_souhaite . "\nBudget estimé: " . $budget_estime;
                    if ($uploadedUrl) {
                        $msg .= "\nDocument joint: " . $uploadedUrl;
                    } elseif ($uploadedRelPath) {
                        $msg .= "\nDocument joint: " . APP_URL . $uploadedRelPath;
                    }
                    MySQLCore::execute(
                        "INSERT INTO contacts (nom, email, telephone, sujet, message) VALUES (?, ?, ?, ?, ?)",
                        [$nom, $email, $telephone, $sujet, $msg]
                    );
                }
                header('Location: ' . APP_URL . 'devis?success=1');
                exit;
            } catch (Throwable $e) {
                $devisError = $e->getMessage();
            }
        }
        $contentPath = BASE_PATH . 'views/devis.php';
        break;
        
    case 'projets':
        $pageTitle = 'Nos Projets';
        $contentPath = BASE_PATH . 'views/projets.php';
        break;
        
    case 'boutique':
        $pageTitle = 'Boutique';
        $contentPath = BASE_PATH . 'views/boutique.php';
        break;
        
    case 'contact':
        $pageTitle = 'Contact';
        if ($method === 'POST') {
            $service = $_GET['service'] ?? 'general';
            
            // Traiter le formulaire de contact général
            if ($service === 'general') {
                $nom = $_POST['nom'] ?? '';
                $email = $_POST['email'] ?? '';
                $telephone = $_POST['telephone'] ?? '';
                $sujet = $_POST['sujet'] ?? '';
                $message = $_POST['message'] ?? '';
                
                if (!empty($nom) && !empty($email) && !empty($message)) {
                    try {
                        $db->execute(
                            "INSERT INTO contacts (nom, email, telephone, sujet, message) 
                             VALUES (?, ?, ?, ?, ?)",
                            [$nom, $email, $telephone, $sujet, $message]
                        );
                        header('Location: ' . APP_URL . 'contact?success=1');
                        exit;
                    } catch (Exception $e) {
                        echo "Erreur: " . $e->getMessage();
                    }
                }
            }
            // Traiter les demandes de devis pour les services
            else if (in_array($service, ['forage', 'plomberie', 'peinture', 'electrification', 'construction', 'materiels'])) {
                $nom = $_POST['nom'] ?? '';
                $email = $_POST['email'] ?? '';
                $telephone = $_POST['telephone'] ?? '';
                $localisation = $_POST['localisation'] ?? '';
                $description = $_POST['description'] ?? '';
                $delai_souhaite = $_POST['delai_souhaite'] ?? '';
                $budget_estime = $_POST['budget_estime'] ?? '';
                
                if (!empty($nom) && !empty($email) && !empty($localisation)) {
                    try {
                        // Enregistrer dans la table appropriée selon le service
                        if ($service === 'forage') {
                            $type_forage = $_POST['type_forage'] ?? '';
                            $db->execute(
                                "INSERT INTO drilling_requests (nom, email, telephone, type_forage, localisation, description, delai_souhaite, budget_estime, statut) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'nouveau')",
                                [$nom, $email, $telephone, $type_forage, $localisation, $description, $delai_souhaite, $budget_estime]
                            );
                        } else {
                            // Pour les autres services, enregistrer dans contacts avec le service en sujet
                            $db->execute(
                                "INSERT INTO contacts (nom, email, telephone, sujet, message) 
                                 VALUES (?, ?, ?, ?, ?)",
                                [$nom, $email, $telephone, "Devis - " . ucfirst($service), $description . "\n\nLocalisation: " . $localisation . "\nDélai souhaité: " . $delai_souhaite . "\nBudget estimé: " . $budget_estime]
                            );
                        }
                        header('Location: ' . APP_URL . 'contact?success=1');
                        exit;
                    } catch (Exception $e) {
                        echo "Erreur: " . $e->getMessage();
                    }
                }
            }
        }
        $contentPath = BASE_PATH . 'views/contact.php';
        break;
        
    case 'admin/login':
        $pageTitle = 'Connexion Admin';
        $error = $loginError;
        $contentPath = BASE_PATH . 'views/admin/login.php';
        break;
        
    case 'admin/dashboard':
    case 'admin/projets':
    case 'admin/produits':
    case 'admin/contacts':
    case 'admin/forages':
    case 'admin/commandes':
    case 'admin/utilisateurs':
    case 'admin/parametres':
    case 'admin/logs':
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['admin_user_id'])) {
            header('Location: ' . APP_URL . 'admin/login');
            exit;
        }
        // Restreindre l'accès à certaines pages aux seuls admins
        $role = $_SESSION['admin_role'] ?? 'editor';
        $adminOnlyPages = ['admin/utilisateurs','admin/logs','admin/parametres','admin/projets'];
        if (in_array($page, $adminOnlyPages, true) && $role !== 'admin') {
            // Journaliser la tentative d'accès interdit
            try {
                $details = json_encode([
                    'page' => $page,
                    'role' => $role,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                    'ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                MySQLCore::execute(
                    "INSERT INTO user_audit (admin_user_id, target_user_id, action, details, created_at) VALUES (?, NULL, 'forbidden_access', ?, NOW())",
                    [$_SESSION['admin_user_id'] ?? null, $details]
                );
            } catch (Throwable $e) { /* ignore logging errors */ }
            http_response_code(403);
            $adminPageTitle = 'Accès interdit';
            $adminContentPath = BASE_PATH . 'views/admin/forbidden.php';
            include BASE_PATH . 'views/admin/layout.php';
            exit;
        }
        
        // Déterminer le titre et le chemin
        $adminPageTitle = match($page) {
            'admin/dashboard' => 'Tableau de Bord',
            'admin/projets' => 'Projets',
            'admin/produits' => 'Produits',
            'admin/contacts' => 'Messages de Contact',
            'admin/forages' => 'Demandes de Forage',
            'admin/commandes' => 'Commandes',
            'admin/utilisateurs' => 'Utilisateurs',
            'admin/parametres' => 'Paramètres',
            'admin/logs' => 'Journal des actions',
            default => 'Admin'
        };
        
        // Déterminer le chemin du contenu
        $adminContentPath = match($page) {
            'admin/dashboard' => BASE_PATH . 'views/admin/dashboard.php',
            'admin/projets' => BASE_PATH . 'views/admin/projets.php',
            'admin/produits' => BASE_PATH . 'views/admin/produits.php',
            'admin/contacts' => BASE_PATH . 'views/admin/contacts.php',
            'admin/forages' => BASE_PATH . 'views/admin/forages.php',
            'admin/commandes' => BASE_PATH . 'views/admin/commandes.php',
            'admin/utilisateurs' => BASE_PATH . 'views/admin/utilisateurs.php',
            'admin/parametres' => BASE_PATH . 'views/admin/parametres.php',
            'admin/logs' => BASE_PATH . 'views/admin/logs.php',
            default => BASE_PATH . 'views/admin/dashboard.php'
        };
        
        include BASE_PATH . 'views/admin/layout.php';
        exit;
        
    case 'admin/logout':
        // Journaliser la déconnexion
        try {
            MySQLCore::execute("CREATE TABLE IF NOT EXISTS user_audit (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_user_id INT NULL,
                target_user_id INT NULL,
                action VARCHAR(50) NOT NULL,
                details TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $details = json_encode([
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            MySQLCore::execute(
                "INSERT INTO user_audit (admin_user_id, target_user_id, action, details) VALUES (?, NULL, 'logout', ?)",
                [$_SESSION['admin_user_id'] ?? null, $details]
            );
        } catch (Throwable $e) { /* ignore */ }
        session_destroy();
        header('Location: ' . APP_URL . 'admin/login');
        exit;
        
    case 'panier':
        $pageTitle = 'Panier';
        $contentPath = BASE_PATH . 'views/panier.php';
        break;
        
    default:
        // Vérifier si c'est un projet spécifique
        if (strpos($page, 'projets/') === 0) {
            $slug = str_replace('projets/', '', $page);
            $project = MySQLCore::fetch(
                    "SELECT id, titre, slug, description, localisation, image_principale, statut 
                     FROM projects WHERE actif = 1 ORDER BY ordre ASC, created_at DESC LIMIT 100"
            );
            
            if ($project) {
                $pageTitle = $project['titre'];
                $contentPath = BASE_PATH . 'views/project-detail.php';
            } else {
                $pageTitle = '404 - Page non trouvée';
                $contentPath = BASE_PATH . 'views/404.php';
            }
        }
        // Vérifier si c'est un produit spécifique
        elseif (strpos($page, 'produit/') === 0) {
            $slug = str_replace('produit/', '', $page);
            $product = MySQLCore::fetch(
                "SELECT * FROM products WHERE slug = ? AND actif = 1",
                [$slug]
            );
            
            if ($product) {
                $pageTitle = $product['nom'];
                $contentPath = BASE_PATH . 'views/product-detail.php';
            } else {
                $pageTitle = '404 - Page non trouvée';
                $contentPath = BASE_PATH . 'views/404.php';
            }
        } else {
            $pageTitle = '404 - Page non trouvée';
            $contentPath = BASE_PATH . 'views/404.php';
        }
}

// Inclusion de la mise en page
include BASE_PATH . 'views/layout.php';
?>
