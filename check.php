<?php
/**
 * Script de vérification de l'installation
 * Ouvrez ce fichier dans le navigateur : http://localhost/kmservices/check.php
 */

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config/config.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification d'Installation - KM Services</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .check-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid;
        }

        .check-item.success {
            background-color: #d4edda;
            border-color: #28a745;
        }

        .check-item.error {
            background-color: #f8d7da;
            border-color: #dc3545;
        }

        .check-item.warning {
            background-color: #fff3cd;
            border-color: #ffc107;
        }

        .check-item.info {
            background-color: #d1ecf1;
            border-color: #17a2b8;
        }

        .check-item i {
            font-size: 24px;
            margin-right: 15px;
            min-width: 30px;
        }

        .check-item.success i {
            color: #28a745;
        }

        .check-item.error i {
            color: #dc3545;
        }

        .check-item.warning i {
            color: #ffc107;
        }

        .check-item.info i {
            color: #17a2b8;
        }

        .check-content {
            flex: 1;
        }

        .check-content h3 {
            margin: 0 0 5px 0;
            font-size: 1.1rem;
        }

        .check-content p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .summary {
            margin-top: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 5px;
            text-align: center;
        }

        .summary h2 {
            margin: 0;
        }

        .next-steps {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .next-steps h3 {
            color: #333;
        }

        .next-steps ol {
            margin: 10px 0;
            padding-left: 20px;
        }

        .next-steps li {
            margin: 8px 0;
            color: #666;
        }

        a {
            color: #667eea;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: #999;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Vérification d'Installation - KM Services</h1>

        <?php
        // Vérification 1: Version PHP
        echo '<div class="check-item ' . (version_compare(PHP_VERSION, '7.4.0') >= 0 ? 'success' : 'error') . '">';
        echo version_compare(PHP_VERSION, '7.4.0') >= 0 ? '✅' : '❌';
        echo '<div class="check-content"><h3>Version PHP</h3>';
        echo '<p>' . PHP_VERSION . ' (Minimum: 7.4.0)</p></div></div>';

        // Vérification 2: Extensions PHP
        $required_extensions = ['pdo', 'pdo_mysql'];
        foreach ($required_extensions as $ext) {
            $loaded = extension_loaded($ext);
            echo '<div class="check-item ' . ($loaded ? 'success' : 'error') . '">';
            echo $loaded ? '✅' : '❌';
            echo '<div class="check-content"><h3>Extension PHP: ' . strtoupper($ext) . '</h3>';
            echo '<p>' . ($loaded ? 'Chargée' : 'Manquante') . '</p></div></div>';
        }

        // Vérification 3: Connexion Base de Données
        try {
            $connection = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS
            );
            echo '<div class="check-item success">';
            echo '✅<div class="check-content"><h3>Base de Données MySQL</h3>';
            echo '<p>Connectée à ' . DB_HOST . ' / ' . DB_NAME . '</p></div></div>';
            
            // Vérification des tables
            $stmt = $connection->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($tables) > 0) {
                echo '<div class="check-item success">';
                echo '✅<div class="check-content"><h3>Tables de Base de Données</h3>';
                echo '<p>' . count($tables) . ' tables trouvées</p></div></div>';
            } else {
                echo '<div class="check-item error">';
                echo '❌<div class="check-content"><h3>Tables de Base de Données</h3>';
                echo '<p>Aucune table trouvée. Importez schema.sql</p></div></div>';
            }
        } catch (PDOException $e) {
            echo '<div class="check-item error">';
            echo '❌<div class="check-content"><h3>Base de Données MySQL</h3>';
            echo '<p>Erreur: ' . htmlspecialchars($e->getMessage()) . '</p></div></div>';
        }

        // Vérification 4: Dossiers et fichiers
        $checks = [
            'config/config.php' => 'Configuration',
            'app/Database.php' => 'Classe Database',
            'public/index.php' => 'Point d\'entrée',
            'views/layout.php' => 'Layout principal',
            'public/assets/css/style.css' => 'Fichier CSS',
            'public/assets/js/main.js' => 'Fichier JavaScript',
        ];

        foreach ($checks as $file => $name) {
            $path = BASE_PATH . $file;
            $exists = file_exists($path);
            echo '<div class="check-item ' . ($exists ? 'success' : 'error') . '">';
            echo $exists ? '✅' : '❌';
            echo '<div class="check-content"><h3>' . $name . '</h3>';
            echo '<p>' . htmlspecialchars($file) . '</p></div></div>';
        }

        // Vérification 5: Permissions
        $uploadDir = BASE_PATH . 'public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $writable = is_writable($uploadDir);
        echo '<div class="check-item ' . ($writable ? 'success' : 'warning') . '">';
        echo $writable ? '✅' : '⚠️';
        echo '<div class="check-content"><h3>Dossier uploads</h3>';
        echo '<p>' . ($writable ? 'Accessible en écriture' : 'Problème de permissions') . '</p></div></div>';

        // Résumé
        $allGood = version_compare(PHP_VERSION, '7.4.0') >= 0 && 
                   extension_loaded('pdo') && 
                   extension_loaded('pdo_mysql') &&
                   file_exists(BASE_PATH . 'public/index.php');

        echo '<div class="summary">';
        if ($allGood) {
            echo '<h2>✅ Installation Complète!</h2>';
            echo '<p style="margin-top: 10px;">Votre installation semble correcte.</p>';
        } else {
            echo '<h2>⚠️ Problèmes Détectés</h2>';
            echo '<p style="margin-top: 10px;">Veuillez corriger les erreurs ci-dessus.</p>';
        }
        echo '</div>';

        echo '<div class="next-steps">';
        echo '<h3>Prochaines Étapes:</h3>';
        echo '<ol>';
        echo '<li><a href="' . APP_URL . '">Visiter le site public</a></li>';
        echo '<li><a href="' . APP_URL . 'admin/login">Accéder à l\'administration</a></li>';
        echo '<li>Consulter le fichier QUICKSTART.md pour commencer</li>';
        echo '<li>Consulter le fichier INSTALLATION.md pour plus de détails</li>';
        echo '</ol>';
        echo '</div>';
        ?>

        <div class="footer">
            <p>KM Services © 2024 - Tous droits réservés</p>
        </div>
    </div>
</body>
</html>
