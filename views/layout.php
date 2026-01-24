<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' : ''; ?>KM Services - Construction & Forage</title>
    <meta name="description" content="KM Services - Expertise en construction, forage et vente de matériels de construction en Haut-Katanga">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <a href="<?php echo APP_URL; ?>">
                    <img src="<?php echo APP_URL; ?>assets/images/logoKMS.png" alt="KM Services Logo" class="navbar-logo">
                    <h1>KM Services</h1>
                </a>
            </div>
            <ul class="navbar-menu">
                <li><a href="<?php echo APP_URL; ?>">Accueil</a></li>
                <li><a href="<?php echo APP_URL; ?>apropos">À Propos</a></li>
                <li><a href="<?php echo APP_URL; ?>services">Services</a></li>
                <li><a href="<?php echo APP_URL; ?>devis" class="btn-admin" style="background:#10b981; color:#fff;">Demande de devis</a></li>
                <li><a href="<?php echo APP_URL; ?>projets">Projets</a></li>
                <li><a href="<?php echo APP_URL; ?>boutique">Boutique</a></li>
                <li><a href="<?php echo APP_URL; ?>contact">Contact</a></li>
                <li><a href="<?php echo APP_URL; ?>admin/login" class="btn-admin">Admin</a></li>
            </ul>
            <button class="navbar-toggle" id="navToggle">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main>
        <?php include $contentPath; ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>KM Services</h3>
                    <p>Expertise en construction,plomberie,l'électrification,<br>la maintenance,le forage ainsi que la fourniture de matériels de construction.</p>
                    <p><strong>Province:</strong> Haut-Katanga et Lualaba, RD Congo</p>
                </div>
                <div class="footer-section">
                    <h3>Services</h3>
                    <ul>
                        <li><a href="<?php echo APP_URL; ?>services#construction">Construction</a></li>
                        <li><a href="<?php echo APP_URL; ?>services#forage">Forage</a></li>
                        <li><a href="<?php echo APP_URL; ?>boutique">Matériels</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Liens Rapides</h3>
                    <ul>
                        <li><a href="<?php echo APP_URL; ?>devis">Demande de devis</a></li>
                        <li><a href="<?php echo APP_URL; ?>projets">Projets</a></li>
                        <li><a href="<?php echo APP_URL; ?>contact">Contact</a></li>
                        <li><a href="<?php echo APP_URL; ?>mentions-legales">Mentions Légales</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <p><i class="fas fa-phone"></i> +243 (0) 892 017 793/+243 (0) 999 920 715</p>
                    <p><i class="fas fa-envelope"></i> contact@kmservices.cd</p>
                    <p><i class="fas fa-map-marker-alt"></i> 235, Kabalo, Q/Makutano, C/Lubumbashi,Haut-Katanga Rép. Dém. du Congo</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 KM Services. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo APP_URL; ?>assets/js/main.js"></script>
</body>
</html>
