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

if (empty($nom) || empty($email) || empty($telephone) || empty($message)) {
    redirect(SITE_URL . '/contact.php');
}

$pdo = getPDO();

try {
    // Essayer d'insérer avec la colonne telephone
    $stmt = $pdo->prepare('INSERT INTO messages_contact (nom, email, telephone, message) VALUES (?, ?, ?, ?)');
    $stmt->execute([$nom, $email, $telephone, $message]);
} catch (PDOException $e) {
    // Si la colonne n'existe pas, insérer sans elle (fallback pour la transition)
    try {
        $stmt = $pdo->prepare('INSERT INTO messages_contact (nom, email, message) VALUES (?, ?, ?)');
        $stmt->execute([$nom, $email, $message]);
    } catch (PDOException $e2) {
        // Erreur de base de données, rediriger avec erreur
        error_log('Contact form error: ' . $e2->getMessage());
        redirect(SITE_URL . '/contact.php?error=1');
    }
}

log_activity('Nouveau message de contact', 'De: ' . $nom . ' (' . $email . ')');
redirect(SITE_URL . '/contact.php?success=1');

