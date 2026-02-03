<?php
require_once __DIR__ . '/../includes/functions.php';

// Log toutes les étapes
$logFile = __DIR__ . '/../logs_contact.txt';
function log_debug($msg) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . ' - ' . $msg . "\n", FILE_APPEND);
}

log_debug('=== DÉBUT TRAITEMENT CONTACT ===');
log_debug('Méthode: ' . $_SERVER['REQUEST_METHOD']);

if (!is_post()) {
    log_debug('Erreur: Pas une requête POST');
    redirect(SITE_URL . '/contact.php');
}

log_debug('Vérification CSRF...');
if (!csrf_verify($_POST['csrf_token'] ?? null)) {
    log_debug('Erreur: Token CSRF invalide');
    redirect(SITE_URL . '/contact.php');
}

$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$message = trim($_POST['message'] ?? '');

log_debug('Données reçues:');
log_debug('  Nom: ' . $nom);
log_debug('  Email: ' . $email);
log_debug('  Téléphone: ' . $telephone);
log_debug('  Message: ' . substr($message, 0, 50) . '...');

if (empty($nom) || empty($email) || empty($message)) {
    log_debug('Erreur: Champs obligatoires vides');
    redirect(SITE_URL . '/contact.php');
}

log_debug('Tentative d\'insertion en BD...');
$pdo = getPDO();

try {
    // Insérer avec les colonnes de base (compatible avec ancienne BD)
    $stmt = $pdo->prepare('INSERT INTO messages_contact (nom, email, message) VALUES (?, ?, ?)');
    $result = $stmt->execute([$nom, $email, $message]);
    
    if ($result) {
        log_debug('✓ Message inséré avec succès');
        
        // Si on a un téléphone et la colonne existe, le mettre à jour
        if (!empty($telephone)) {
            try {
                $lastId = $pdo->lastInsertId();
                $updateStmt = $pdo->prepare('UPDATE messages_contact SET telephone = ? WHERE id = ?');
                $updateStmt->execute([$telephone, $lastId]);
                log_debug('✓ Téléphone mis à jour');
            } catch (PDOException $e) {
                log_debug('⚠ Colonne téléphone n\'existe pas: ' . $e->getMessage());
            }
        }
        
        log_activity('Nouveau message de contact', 'De: ' . $nom . ' (' . $email . ')');
        log_debug('✓ Activité enregistrée');
        log_debug('=== FIN TRAITEMENT OK ===');
    } else {
        log_debug('✗ Erreur d\'exécution');
    }
} catch (PDOException $e) {
    log_debug('✗ Erreur PDO: ' . $e->getMessage());
    log_debug('=== FIN TRAITEMENT ERROR ===');
    redirect(SITE_URL . '/contact.php?error=1');
}

log_debug('Redirection vers contact.php?success=1');
redirect(SITE_URL . '/contact.php?success=1');