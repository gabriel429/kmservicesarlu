<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(0);
ini_set('display_errors', 0);
session_start();

$response = ['success' => false, 'message' => 'Erreur'];

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/MySQL.php';

function ensureContactAuditTable() {
    MySQLCore::execute(
        "CREATE TABLE IF NOT EXISTS contact_audit (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_user_id INT NULL,
            contact_id INT NULL,
            action VARCHAR(50) NOT NULL,
            details TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function logContactAction($action, $contactId = null, $details = null) {
    ensureContactAuditTable();
    $adminId = $_SESSION['admin_user_id'] ?? null;
    MySQLCore::execute(
        "INSERT INTO contact_audit (admin_user_id, contact_id, action, details) VALUES (?, ?, ?, ?)",
        [$adminId, $contactId, $action, $details]
    );
}
try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            if (!$id) throw new Exception('ID requis');
            
            $sql = "SELECT id, nom, email, telephone, sujet, message, statut, created_at FROM contacts WHERE id = ?";
            $contact = MySQLCore::fetch($sql, [$id]);
            
            if (!$contact) throw new Exception('Message non trouvé');
            
            $response = ['success' => true, 'data' => $contact];
            break;
            
        case 'update_status':
            if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
                throw new Exception('Accès refusé: réservé aux administrateurs');
            }
            $id = intval($_POST['id'] ?? 0);
            $statut = $_POST['statut'] ?? 'nouveau';
            if (!$id) throw new Exception('ID requis');
            
            MySQLCore::execute("UPDATE contacts SET statut = ? WHERE id = ?", [$statut, $id]);
            $response = ['success' => true, 'message' => 'Statut mis à jour'];
            logContactAction('update_status', $id, json_encode(['statut' => $statut]));
            break;
            
        case 'delete':
            if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
                throw new Exception('Accès refusé: réservé aux administrateurs');
            }
            $id = intval($_POST['id'] ?? 0);
            if (!$id) throw new Exception('ID requis');
            
            MySQLCore::execute("DELETE FROM contacts WHERE id = ?", [$id]);
            $response = ['success' => true, 'message' => 'Supprimé'];
            logContactAction('delete_contact', $id, null);
            break;
            
        case 'reply':
            if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
                throw new Exception('Accès refusé: réservé aux administrateurs');
            }
            $id = intval($_POST['id'] ?? 0);
            $email = $_POST['email'] ?? '';
            $nom = $_POST['nom'] ?? '';
            $message = $_POST['message'] ?? '';
            
            if (!$id || !$email || !$message) throw new Exception('Données incomplètes');
            
            $subject = "Réponse à votre message - KM Services";
            $headers = "From: contact@kmservicesarlu.cd\r\nContent-Type: text/plain; charset=UTF-8\r\n";
            $body = "Bonjour " . htmlspecialchars($nom) . ",\n\n";
            $body .= "Voici la réponse à votre message:\n\n";
            $body .= htmlspecialchars($message) . "\n\n";
            $body .= "Cordialement,\nL'équipe KM Services";
            
            @mail($email, $subject, $body, $headers);
            MySQLCore::execute("UPDATE contacts SET statut = ? WHERE id = ?", ['traite', $id]);
            
            $response = ['success' => true, 'message' => 'Réponse envoyée'];
            logContactAction('reply_contact', $id, json_encode(['email' => $email]));
            break;
            
        default:
            throw new Exception('Action invalide: ' . $action);
    }
    
} catch (Throwable $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
exit;
