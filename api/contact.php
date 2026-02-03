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
$message = trim($_POST['message'] ?? '');

if (empty($nom) || empty($email) || empty($message)) {
    redirect(SITE_URL . '/contact.php');
}

$pdo = getPDO();
$stmt = $pdo->prepare('INSERT INTO messages_contact (nom, email, message) VALUES (?, ?, ?)');
$stmt->execute([$nom, $email, $message]);

redirect(SITE_URL . '/contact.php?success=1');
