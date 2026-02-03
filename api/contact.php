<?php
require_once __DIR__ . '/../includes/functions.php';

if (!is_post()) {
    redirect(SITE_URL . '/contact.php');
}

if (!csrf_verify($_POST['csrf_token'] ?? null)) {
    redirect(SITE_URL . '/contact.php');
}

$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($nom) || empty($email) || empty($message)) {
    redirect(SITE_URL . '/contact.php');
}

$pdo = getPDO();

try {
    // Insérer avec les colonnes de base (compatible avec ancienne BD)
    $stmt = $pdo->prepare('INSERT INTO messages_contact (nom, email, message) VALUES (?, ?, ?)');
    $stmt->execute([$nom, $email, $message]);
    
    // Si on a un téléphone et la colonne existe, le mettre à jour
    if (!empty($telephone)) {
        try {
            $lastId = $pdo->lastInsertId();
            $updateStmt = $pdo->prepare('UPDATE messages_contact SET telephone = ? WHERE id = ?');
            $updateStmt->execute([$telephone, $lastId]);
        } catch (PDOException $e) {
            // La colonne n'existe pas, ce n'est pas grave
            error_log('Telephone column update failed: ' . $e->getMessage());
        }
    }
    
    log_activity('Nouveau message de contact', 'De: ' . $nom . ' (' . $email . ')');
} catch (PDOException $e) {
    error_log('Contact form error: ' . $e->getMessage());
    redirect(SITE_URL . '/contact.php?error=1');
}

redirect(SITE_URL . '/contact.php?success=1');