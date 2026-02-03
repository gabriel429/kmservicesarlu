<?php
require_once __DIR__ . '/functions.php';
$page_title = $page_title ?? 'KM SERVICES';
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<header class="site-header">
    <div class="topbar text-white py-2">
        <div class="container d-flex justify-content-between">
            <span><i class="fa-solid fa-phone"></i> +243 (0) 892 017 793 / 999 920 715</span>
            <span><i class="fa-solid fa-location-dot"></i> Lubumbashi, RDC</span>
        </div>
    </div>
    <nav class="navbar navbar-expand-lg navbar-dark bg-secondary">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= SITE_URL ?>/index.php">
                <img src="<?= SITE_URL ?>/assets/images/logoKMS.png" alt="Logo KM SERVICES" class="navbar-logo">
                <span>KM SERVICES</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link <?= $current === 'index.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/index.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link <?= $current === 'about.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/about.php">À propos</a></li>
                    <li class="nav-item"><a class="nav-link <?= $current === 'services.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/services.php">Services</a></li>
                    <li class="nav-item"><a class="nav-link <?= $current === 'projects.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/projects.php">Projets</a></li>
                    <li class="nav-item"><a class="nav-link <?= $current === 'products.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/products.php">Produits</a></li>
                    <li class="nav-item"><a class="nav-link <?= $current === 'contact.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/contact.php">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<main>
