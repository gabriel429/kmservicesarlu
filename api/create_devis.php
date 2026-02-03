<?php
require_once __DIR__ . '/../includes/functions.php';

if (!is_post()) {
    redirect(SITE_URL . '/devis.php');
}

if (!csrf_verify($_POST['csrf_token'] ?? null)) {
    redirect(SITE_URL . '/devis.php');
}

$clientInfo = [
    'nom' => trim($_POST['nom'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'telephone' => trim($_POST['telephone'] ?? ''),
];

$serviceId = (int) ($_POST['service_id'] ?? 0);
$description = trim($_POST['description'] ?? '');

if (!$serviceId || empty($clientInfo['nom']) || empty($clientInfo['email']) || empty($clientInfo['telephone']) || empty($description)) {
    redirect(SITE_URL . '/devis.php');
}

$pdo = getPDO();
$stmt = $pdo->prepare('INSERT INTO demandes_devis (service_id, client_info, description) VALUES (?, ?, ?)');
$stmt->execute([$serviceId, json_encode($clientInfo, JSON_UNESCAPED_UNICODE), $description]);

redirect(SITE_URL . '/devis.php?success=1');
