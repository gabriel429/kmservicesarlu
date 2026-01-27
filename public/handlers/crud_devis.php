<?php
/**
 * Handler pour la gestion des demandes de devis
 */
session_start();
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/app/MySQL.php';
require_once dirname(__DIR__, 2) . '/app/helpers.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? 'list';

try {
    // Garantir que la table quote_requests existe
    ensureQuoteRequestsTableExists();
    
    // Assurer que la table quote_requests existe
    try {
        MySQLCore::execute(
            "CREATE TABLE IF NOT EXISTS quote_requests (
                id INT PRIMARY KEY AUTO_INCREMENT,
                numero_devis VARCHAR(50) UNIQUE NOT NULL,
                nom VARCHAR(150) NOT NULL,
                email VARCHAR(150) NOT NULL,
                telephone VARCHAR(20) NOT NULL,
                localisation VARCHAR(255),
                service VARCHAR(100),
                type_service VARCHAR(100),
                description LONGTEXT,
                delai_souhaite VARCHAR(100),
                budget_estime DECIMAL(12, 2),
                document_joint VARCHAR(255),
                statut ENUM('nouveau', 'en_attente', 'contacte', 'accepte', 'refuse') DEFAULT 'nouveau',
                lu TINYINT DEFAULT 0,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                treated_by INT DEFAULT NULL,
                FOREIGN KEY (treated_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_statut (statut),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    } catch (Throwable $te) {
        // Table existe déjà
    }

    if ($action === 'create') {
        // Créer une nouvelle demande de devis
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $localisation = trim($_POST['localisation'] ?? '');
        $service = trim($_POST['service'] ?? 'general');
        $type_service = trim($_POST['type_forage'] ?? $_POST['type_service'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $delai_souhaite = trim($_POST['delai_souhaite'] ?? '');
        $budget_estime = isset($_POST['budget_estime']) ? (float)$_POST['budget_estime'] : null;

        if (!$nom || !$email || !$telephone || !$localisation) {
            throw new Exception('Données requises manquantes');
        }

        // Générer le numéro de devis
        $numero_devis = 'DEV-' . date('YmdHis') . '-' . rand(1000, 9999);

        // Traiter le fichier joint s'il existe
        $document_joint = null;
        if (isset($_FILES['document_joint']) && $_FILES['document_joint']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['document_joint'];
            $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
            
            if (in_array($file['type'], $allowed_types) && $file['size'] <= 5242880) { // 5 Mo
                $filename = 'quote_' . time() . '_' . basename($file['name']);
                $upload_dir = dirname(__DIR__) . '/uploads/devis/';
                
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                    $document_joint = 'devis/' . $filename;
                }
            }
        }

        // Insérer la demande
        MySQLCore::execute(
            "INSERT INTO quote_requests (numero_devis, nom, email, telephone, localisation, service, type_service, description, delai_souhaite, budget_estime, document_joint, statut) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'nouveau')",
            [$numero_devis, $nom, $email, $telephone, $localisation, $service, $type_service, $description, $delai_souhaite, $budget_estime, $document_joint]
        );

        // Envoyer un email de confirmation
        $subject = 'Demande de devis reçue - ' . $numero_devis;
        $body = "Bonjour $nom,\n\n";
        $body .= "Nous avons bien reçu votre demande de devis.\n\n";
        $body .= "Numéro de demande: $numero_devis\n";
        $body .= "Service: $service\n";
        $body .= "Localisation: $localisation\n\n";
        $body .= "Nous analyserons votre demande et vous contacterons bientôt pour vous proposer un devis détaillé.\n\n";
        $body .= "Cordialement,\n";
        $body .= "L'équipe KM Services\n";
        $body .= "contact@kmservices.cd\n";

        $headers = "From: contact@kmservices.cd\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "Reply-To: contact@kmservices.cd\r\n";

        @mail($email, $subject, $body, $headers);

        echo json_encode([
            'success' => true,
            'message' => 'Demande de devis créée avec succès',
            'numero_devis' => $numero_devis
        ]);

    } elseif ($action === 'get') {
        // Récupérer les détails d'une demande
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $quote = MySQLCore::fetch(
            "SELECT * FROM quote_requests WHERE id = ?",
            [$id]
        );

        if (!$quote) {
            throw new Exception('Demande non trouvée');
        }

        echo json_encode([
            'success' => true,
            'data' => $quote
        ]);

    } elseif ($action === 'update_status') {
        // Mettre à jour le statut
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $statut = trim($_POST['statut'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (!in_array($statut, ['nouveau', 'en_attente', 'contacte', 'accepte', 'refuse'])) {
            throw new Exception('Statut invalide');
        }

        MySQLCore::execute(
            "UPDATE quote_requests SET statut = ?, notes = ?, treated_by = ?, lu = 1 WHERE id = ?",
            [$statut, $notes, $_SESSION['admin_id'] ?? null, $id]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Statut mis à jour'
        ]);

    } elseif ($action === 'delete') {
        // Supprimer une demande
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        MySQLCore::execute(
            "DELETE FROM quote_requests WHERE id = ?",
            [$id]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Demande supprimée'
        ]);

    } else {
        throw new Exception('Action inconnue');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
