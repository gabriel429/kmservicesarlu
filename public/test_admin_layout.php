<?php
// Simple test to check admin layout
session_start();
$_SESSION['admin_username'] = 'TestUser';
$_SESSION['admin_role'] = 'admin';

// Mock the required constants and setup
define('APP_URL', 'http://localhost/kmservices/');
define('ASSET_URL', APP_URL . 'public/');

$adminPageTitle = 'Test Dashboard';
$adminContentPath = null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Layout Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #f59e0b;
            --dark-bg: #111827;
            --light-bg: #f9fafb;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
        }

        .admin-container {
            display: block;
            min-height: 100vh;
        }

        .admin-main-content {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin-left: 250px;
        }

        .admin-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--secondary-color);
            font-size: 1.75rem;
            cursor: pointer;
            padding: 0.5rem;
            margin-right: 1rem;
            transition: color 0.3s ease;
        }

        .admin-menu-toggle:hover {
            color: var(--primary-color);
        }

        .admin-header {
            background-color: white;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-start;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .admin-header h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
        }

        .admin-sidebar {
            background-color: var(--dark-bg);
            color: white;
            padding: 2rem 0;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }

        .admin-sidebar h2 {
            padding: 0 1.5rem;
            margin-bottom: 2rem;
            font-size: 1.2rem;
            color: var(--secondary-color);
        }

        .admin-sidebar ul {
            list-style: none;
        }

        .admin-sidebar li {
            margin-bottom: 0.5rem;
        }

        .admin-sidebar a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: #d1d5db;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .admin-sidebar a:hover,
        .admin-sidebar a.active {
            background-color: var(--primary-color);
            color: var(--secondary-color);
            padding-left: 2rem;
        }

        .admin-content {
            padding: 2rem;
            flex: 1;
        }

        .test-box {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .test-box h2 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        @media (max-width: 1024px) {
            .admin-main-content {
                margin-left: 0;
            }

            .admin-sidebar {
                width: 100%;
                height: auto;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                max-height: 0;
                overflow: hidden;
                background-color: var(--dark-bg);
                border-top: 1px solid var(--border-color);
                z-index: 999;
                transition: max-height 0.3s ease;
            }

            .admin-sidebar.active {
                max-height: 65vh;
                overflow-y: auto;
            }

            .admin-menu-toggle {
                display: block;
            }

            .admin-main-content {
                margin-bottom: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <h2>KM Services</h2>
            <ul>
                <li><a href="#dashboard">Tableau de Bord</a></li>
                <li><a href="#projets">Projets</a></li>
                <li><a href="#produits">Produits</a></li>
                <li><a href="#contacts">Messages</a></li>
                <li><a href="#forages">Demandes de Forage</a></li>
                <li><a href="#commandes">Commandes</a></li>
                <li><a href="#utilisateurs">Utilisateurs</a></li>
                <li><a href="#logs">Journal des actions</a></li>
                <li><a href="#logout" style="color: #ef4444;">Déconnexion</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="admin-main-content">
            <header class="admin-header">
                <button class="admin-menu-toggle" id="adminMenuToggle" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1><?php echo htmlspecialchars($adminPageTitle); ?></h1>
                <div style="color: var(--text-secondary);">
                    <span>TestUser (admin)</span> | 
                    <a href="#" style="color: inherit;">Voir le site</a>
                </div>
            </header>

            <main class="admin-content">
                <div class="test-box">
                    <h2>✅ Menu Sidebar Visible</h2>
                    <p>Si vous voyez le menu noir à gauche sur l'écran desktop, la correction fonctionne !</p>
                    <p>Le menu devrait avoir une largeur de 250px et être fixe sur le côté gauche.</p>
                </div>

                <div class="test-box">
                    <h2>📱 Test Responsive</h2>
                    <p>Redimensionnez votre navigateur :</p>
                    <ul style="margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li><strong>Desktop (&gt;1024px):</strong> Menu à gauche, contenu avec margin-left</li>
                        <li><strong>Tablet/Mobile (&lt;=1024px):</strong> Menu hamburger, drawer en bas</li>
                    </ul>
                </div>

                <div class="test-box">
                    <h2>Viewport Info</h2>
                    <p>Largeur: <strong id="width">-</strong>px | Hauteur: <strong id="height">-</strong>px</p>
                </div>
            </main>
        </div>
    </div>

    <script>
        function updateViewportInfo() {
            document.getElementById('width').textContent = window.innerWidth;
            document.getElementById('height').textContent = window.innerHeight;
        }

        window.addEventListener('resize', updateViewportInfo);
        window.addEventListener('load', updateViewportInfo);

        // Admin Mobile Menu Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const adminMenuToggle = document.getElementById('adminMenuToggle');
            const adminSidebar = document.querySelector('.admin-sidebar');
            
            if (adminMenuToggle) {
                adminMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    adminSidebar.classList.toggle('active');
                    
                    const icon = adminMenuToggle.querySelector('i');
                    if (adminSidebar.classList.contains('active')) {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times');
                    } else {
                        icon.classList.add('fa-bars');
                        icon.classList.remove('fa-times');
                    }
                });

                const sidebarLinks = adminSidebar.querySelectorAll('a');
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        adminSidebar.classList.remove('active');
                        const icon = adminMenuToggle.querySelector('i');
                        icon.classList.add('fa-bars');
                        icon.classList.remove('fa-times');
                    });
                });

                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.admin-sidebar') && !e.target.closest('.admin-menu-toggle')) {
                        adminSidebar.classList.remove('active');
                        const icon = adminMenuToggle.querySelector('i');
                        icon.classList.add('fa-bars');
                        icon.classList.remove('fa-times');
                    }
                });

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        adminSidebar.classList.remove('active');
                        const icon = adminMenuToggle.querySelector('i');
                        icon.classList.add('fa-bars');
                        icon.classList.remove('fa-times');
                    }
                });
            }
        });
    </script>
</body>
</html>
