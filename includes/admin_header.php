<?php
require_once __DIR__ . '/auth.php';
require_login();
$admin_title = $admin_title ?? 'Administration';
$current_admin = basename($_SERVER['PHP_SELF']);
$userRole = $_SESSION['user']['role'] ?? 'editor';
$isEditor = $userRole === 'editor';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($admin_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="admin-body">
<div class="admin-layout d-flex">
    <aside class="admin-sidebar">
        <h5 class="text-white px-3 pt-3">KM Admin</h5>
        <a class="<?= $current_admin === 'dashboard.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/admin/dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <?php if (!$isEditor): ?>
            <a class="<?= $current_admin === 'gestion_services.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/admin/gestion_services.php"><i class="fa-solid fa-briefcase"></i> Services</a>
        <?php endif; ?>
        <a class="<?= $current_admin === 'gestion_produits.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/admin/gestion_produits.php"><i class="fa-solid fa-box"></i> Produits</a>
        <a class="<?= $current_admin === 'gestion_projets.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/admin/gestion_projets.php"><i class="fa-solid fa-city"></i> Projets</a>
        <?php if (!$isEditor): ?>
            <a class="<?= $current_admin === 'gestion_partenaires.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/admin/gestion_partenaires.php"><i class="fa-solid fa-handshake"></i> Partenaires</a>
        <?php endif; ?>
        <a class="<?= $current_admin === 'gestion_devis.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/admin/gestion_devis.php"><i class="fa-solid fa-file-signature"></i> Devis</a>
        <a class="<?= $current_admin === 'gestion_commandes.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/admin/gestion_commandes.php"><i class="fa-brands fa-whatsapp"></i> Commandes</a>
        <?php if (!$isEditor): ?>
            <a class="<?= $current_admin === 'gestion_utilisateurs.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/admin/gestion_utilisateurs.php"><i class="fa-solid fa-users"></i> Utilisateurs</a>
            <a class="<?= $current_admin === 'gestion_activite.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/admin/gestion_activite.php"><i class="fa-solid fa-clock-rotate-left"></i> Journal</a>
            <a class="<?= $current_admin === 'parametres.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/admin/parametres.php"><i class="fa-solid fa-gear"></i> Paramètres</a>
        <?php endif; ?>
        <a href="<?= SITE_URL ?>/admin/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
    </aside>
    <main class="flex-grow-1 p-4">
