<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; font-src 'self' data: https://cdnjs.cloudflare.com; img-src 'self' data: blob:;">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' : ''; ?>KM Services - Construction & Forage</title>
    <meta name="description" content="KM Services - Expertise en construction, forage et vente de matériels de construction en Haut-Katanga">
    <link rel="stylesheet" href="<?php echo ASSET_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <a href="<?php echo APP_URL; ?>">
                    <img src="<?php echo ASSET_URL; ?>assets/images/logoKMS.png" alt="KM Services Logo" class="navbar-logo">
                    <h1>KM Services</h1>
                </a>
            </div>
            <ul class="navbar-menu">
                <li><a href="<?php echo APP_URL; ?>">Accueil</a></li>
                <li><a href="<?php echo APP_URL; ?>apropos">À Propos</a></li>
                <li><a href="<?php echo APP_URL; ?>services">Services</a></li>
                <li><a href="<?php echo APP_URL; ?>projets">Projets</a></li>
                <li><a href="<?php echo APP_URL; ?>boutique">Boutique</a></li>
                <li><a href="<?php echo APP_URL; ?>contact">Contact</a></li>
                <li><a href="<?php echo APP_URL; ?>admin/login" class="" style="width: 100%; padding: 0.75rem; background-color: #1e3a8a; color: white; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; font-size: 1rem; transition: all 0.3s ease;">Admin</a></li>
            </ul>
            <button class="navbar-toggle" id="navToggle">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main>
        <?php echo $content; ?>
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
                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #94a3b8;">Développée par <a href="https://www.akilig.com/" target="_blank" rel="noopener noreferrer" style="color: #0ea5e9; text-decoration: none; font-weight: 600;">Akili Groupe</a></p>
            </div>
        </div>
    </footer>

    <script src="<?php echo ASSET_URL; ?>assets/js/main.js"></script>
</body>
</html>
