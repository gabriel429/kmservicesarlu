<!DOCTYPE html>
<html lang="fr">
<?php 
// Ensure MySQLCore is available
if (!class_exists('MySQLCore')) {
    require_once __DIR__ . '/../../app/MySQL.php';
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com; img-src 'self' data: blob:;">
    <title><?php echo isset($adminPageTitle) ? $adminPageTitle . ' | ' : ''; ?>Admin - KM Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #f59e0b;
            --accent-color: #10b981;
            --dark-bg: #111827;
            --light-bg: #f9fafb;
            --text-primary: #1f2937;
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
            grid-column: 2;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin-left: 250px;
        }

        /* Mobile Menu Toggle Button */
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
            width: auto;
            height: auto;
            line-height: 1;
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
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
            max-height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
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
            margin-left: 0;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .admin-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }

        .admin-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .admin-table tr:hover {
            background-color: var(--light-bg);
        }

        .btn-small {
            padding: 0.5rem 1rem;
            margin-right: 0.5rem;
            border: none;
            border-radius: 0.25rem;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-success {
            background-color: var(--accent-color);
            color: white;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.25rem;
            font-family: inherit;
            font-size: 1rem;
        }

        .card {
            background-color: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }

        .card h2 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .stat-card {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-right: 1rem;
            min-width: 200px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            font-size: 0.9rem;
        }

        /* ============================================================================
           RESPONSIVE DESIGN - TABLET (1024px)
           ============================================================================ */
        @media (max-width: 1024px) {
            .admin-main-content {
                margin-left: 0;
                padding-bottom: 0;
            }

            .admin-sidebar {
                width: 100%;
                height: auto;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                top: auto;
                max-height: 0;
                overflow: hidden;
                background-color: var(--dark-bg);
                border-top: 2px solid var(--secondary-color);
                z-index: 999;
                transition: max-height 0.3s ease-in-out;
                visibility: hidden;
            }

            .admin-sidebar.active {
                max-height: 60vh;
                overflow-y: auto;
                visibility: visible;
            }

            .admin-sidebar h2 {
                padding: 1rem 1.5rem;
                margin-bottom: 0.5rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .admin-sidebar ul {
                padding: 0;
            }

            .admin-sidebar li {
                margin-bottom: 0;
            }

            .admin-sidebar a {
                font-size: 0.9rem;
                padding: 0.75rem 1.5rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            }

            .admin-menu-toggle {
                display: block !important;
            }

            .admin-main-content {
                margin-bottom: 0;
                padding-bottom: calc(65vh + 1rem);
            }

            .admin-content {
                padding: 1rem;
                margin-bottom: 1rem;
            }

            .admin-table {
                font-size: 0.85rem;
            }

            .admin-table th,
            .admin-table td {
                padding: 0.75rem 0.5rem;
            }

            .form-group input,
            .form-group textarea,
            .form-group select {
                font-size: 16px;
            }

            .card {
                padding: 1rem;
                margin-bottom: 1rem;
            }

            .btn-small {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
                margin-right: 0.25rem;
                margin-bottom: 0.25rem;
            }
        }

        /* ============================================================================
           RESPONSIVE DESIGN - MOBILE (768px and below)
           ============================================================================ */
        @media (max-width: 768px) {
            .admin-container {
                grid-template-columns: 1fr;
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
                border-top: 2px solid var(--secondary-color);
                z-index: 999;
                transition: max-height 0.3s ease-in-out;
                visibility: hidden;
                pointer-events: none;
            }

            .admin-sidebar.active {
                max-height: 60vh;
                overflow-y: auto;
                visibility: visible;
                pointer-events: auto;
            }

            .admin-sidebar h2 {
                padding: 1rem 1.5rem;
                margin-bottom: 0.5rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .admin-sidebar ul {
                padding: 0;
            }

            .admin-sidebar li {
                margin-bottom: 0;
            }

            .admin-sidebar a {
                font-size: 0.9rem;
                padding: 0.75rem 1.5rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            }

            .admin-menu-toggle {
                display: block;
                order: -1;
                margin-right: 0.5rem;
                font-size: 1.5rem;
                padding: 0.25rem 0.5rem;
            }

            .admin-header {
                flex-direction: row;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem 1rem;
            }

            .admin-header h1 {
                font-size: 1.1rem;
                margin: 0;
                order: 0;
                flex: 1;
            }

            .admin-header > div {
                font-size: 0.75rem;
                order: 2;
                width: 100%;
                text-align: left;
            }

            .admin-main-content {
                margin-bottom: 0;
                padding-bottom: calc(65vh + 1rem);
            }

            .admin-content {
                padding: 0.75rem;
                padding-bottom: 1rem;
                margin-bottom: 1rem;
            }

            .admin-table {
                font-size: 0.75rem;
                width: 100%;
                overflow-x: auto;
            }

            .admin-table th,
            .admin-table td {
                padding: 0.5rem 0.25rem;
                white-space: nowrap;
            }

            .admin-table th {
                font-size: 0.7rem;
            }

            /* Horizontal scrolling for tables */
            .admin-table {
                display: block;
                overflow-x: auto;
            }

            .admin-table thead,
            .admin-table tbody,
            .admin-table th,
            .admin-table td,
            .admin-table tr {
                display: block;
            }

            .admin-table thead {
                display: none;
            }

            .admin-table tbody tr {
                border: 1px solid var(--border-color);
                margin-bottom: 1rem;
                border-radius: 0.25rem;
            }

            .admin-table td {
                padding-left: 50%;
                position: relative;
                border: none;
                border-bottom: 1px solid var(--border-color);
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
                padding-right: 0.5rem;
                word-wrap: break-word;
            }

            .admin-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 48%;
                padding: 0.5rem;
                font-weight: 600;
                font-size: 0.8rem;
                word-wrap: break-word;
                pointer-events: none;
            }

            .admin-table td[data-label="Actions"] {
                padding-left: 100%;
            }

            .admin-table td[data-label="Actions"]::before {
                width: 100%;
            }

            .admin-table .btn {
                display: inline-block;
                padding: 0.35rem 0.5rem;
                font-size: 0.7rem;
                margin-right: 0.25rem;
                margin-bottom: 0.25rem;
                width: auto;
            }

            .admin-table .badge {
                display: inline-block;
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .form-group label {
                font-size: 0.9rem;
                margin-bottom: 0.3rem;
            }

            .form-group input,
            .form-group textarea,
            .form-group select {
                padding: 0.6rem;
                font-size: 16px;
            }

            .form-row {
                flex-direction: column;
                gap: 0.75rem;
            }

            .form-row input {
                width: 100%;
            }

            .card {
                padding: 0.75rem;
                margin-bottom: 0.75rem;
            }

            .card h2 {
                font-size: 1.1rem;
                margin-bottom: 0.75rem;
            }

            .card h3 {
                font-size: 1rem;
                margin-bottom: 0.5rem;
            }

            .btn-small {
                padding: 0.35rem 0.6rem;
                font-size: 0.7rem;
                margin-right: 0.15rem;
                margin-bottom: 0.2rem;
                display: inline-block;
                min-width: auto;
                white-space: normal;
                word-wrap: break-word;
            }

            .btn-primary,
            .btn-success,
            .btn-danger {
                display: inline-block;
                padding: 0.5rem 0.75rem;
                margin-bottom: 0.25rem;
                font-size: 0.85rem;
            }

            /* Actions cell styling */
            .admin-table td[data-label="Actions"] {
                padding-left: 50%;
            }

            .admin-table td[data-label="Actions"]::before {
                content: "Actions";
                position: absolute;
                left: 0;
                width: 50%;
                padding: 0.5rem;
                font-weight: 600;
                background-color: var(--light-bg);
            }

            /* Button group responsive */
            .btn-group,
            .action-buttons {
                display: flex;
                gap: 0.25rem;
                flex-wrap: wrap;
            }

            .btn-group .btn,
            .action-buttons .btn {
                flex-shrink: 1;
                padding: 0.3rem 0.5rem;
                font-size: 0.7rem;
                margin-bottom: 0.2rem;
            }

            .stat-card h3 {
                font-size: 1.5rem;
                margin-bottom: 0.3rem;
            }

            .stat-card p {
                font-size: 0.75rem;
            }

            /* Stats container scrollable on mobile */
            .stats-container {
                display: flex;
                gap: 0.5rem;
                margin-bottom: 1rem;
                overflow-x: auto;
                padding-bottom: 0.5rem;
            }
        }

        /* ============================================================================
           RESPONSIVE DESIGN - SMALL MOBILE (480px and below)
           ============================================================================ */
        @media (max-width: 480px) {
            .admin-header h1 {
                font-size: 0.95rem;
            }

            .admin-header div {
                font-size: 0.65rem;
            }

            .admin-table {
                font-size: 0.65rem;
            }

            .admin-table td {
                padding-left: 45%;
                padding-top: 0.4rem;
                padding-bottom: 0.4rem;
                padding-right: 0.3rem;
            }

            .admin-table td::before {
                width: 43%;
                font-size: 0.65rem;
                padding: 0.3rem;
                pointer-events: none;
            }

            .admin-table td[data-label="Actions"] {
                padding-left: 100%;
            }

            .admin-table td[data-label="Actions"]::before {
                width: 100%;
            }

            .admin-table .btn {
                padding: 0.25rem 0.35rem;
                font-size: 0.6rem;
                margin-right: 0.15rem;
                margin-bottom: 0.15rem;
            }

            .admin-table .badge {
                padding: 0.2rem 0.35rem;
                font-size: 0.6rem;
            }

            .card {
                padding: 0.5rem;
            }

            .card h2,
            .card h3 {
                font-size: 0.9rem;
            }

            .btn-small {
                padding: 0.3rem 0.5rem;
                font-size: 0.6rem;
            }

            .form-group input,
            .form-group textarea,
            .form-group select {
                padding: 0.5rem;
                font-size: 16px;
            }

            .stat-card {
                min-width: 120px;
                padding: 0.75rem;
                margin-right: 0.25rem;
                font-size: 0.75rem;
            }

            .stat-card h3 {
                font-size: 1.2rem;
                margin-bottom: 0.2rem;
            }

            .stat-card p {
                font-size: 0.65rem;
            }

            .admin-sidebar h2 {
                padding: 0.75rem 1rem;
                margin-bottom: 0.5rem;
                font-size: 1rem;
            }

            .admin-sidebar a {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }
        }
    </style>
    <script>
        const APP_URL = '<?php echo APP_URL; ?>';
        const ASSET_URL = '<?php echo ASSET_URL; ?>';

        // Admin Mobile Menu Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const adminMenuToggle = document.getElementById('adminMenuToggle');
            const adminSidebar = document.querySelector('.admin-sidebar');
            
            if (!adminMenuToggle || !adminSidebar) {
                console.error('Admin menu elements not found');
                return;
            }

            // Toggle menu on button click
            adminMenuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                adminSidebar.classList.toggle('active');
                updateIcon();
                console.log('Menu toggled, active:', adminSidebar.classList.contains('active'));
            });

            function updateIcon() {
                const icon = adminMenuToggle.querySelector('i');
                if (!icon) return;
                
                if (adminSidebar.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.add('fa-bars');
                    icon.classList.remove('fa-times');
                }
            }

            // Close menu when clicking a link
            const sidebarLinks = adminSidebar.querySelectorAll('a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Don't prevent default, let the navigation happen
                    adminSidebar.classList.remove('active');
                    updateIcon();
                });
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (adminSidebar.classList.contains('active')) {
                    if (!e.target.closest('.admin-sidebar') && !e.target.closest('.admin-menu-toggle')) {
                        adminSidebar.classList.remove('active');
                        updateIcon();
                    }
                }
            });

            // Close menu on escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && adminSidebar.classList.contains('active')) {
                    adminSidebar.classList.remove('active');
                    updateIcon();
                }
            });

            // Mark active page
            const currentUrl = window.location.pathname;
            sidebarLinks.forEach(link => {
                const linkPath = link.getAttribute('href');
                if (linkPath && currentUrl.includes(linkPath)) {
                    link.classList.add('active');
                }
            });

            console.log('Admin menu initialized successfully');
        });
    </script>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <h2>KM Services</h2>
            <ul>
                <li><a href="<?php echo APP_URL; ?>admin/dashboard">Tableau de Bord</a></li>
                <?php $role = $_SESSION['admin_role'] ?? 'editor'; ?>
                <?php if ($role === 'admin'): ?>
                    <li><a href="<?php echo APP_URL; ?>admin/projets">Projets</a></li>
                <?php endif; ?>
                <li><a href="<?php echo APP_URL; ?>admin/produits">Produits</a></li>
                <li><a href="<?php echo APP_URL; ?>admin/contacts">Messages</a></li>
                <li><a href="<?php echo APP_URL; ?>admin/forages">Demandes de Forage</a></li>
                <li><a href="<?php echo APP_URL; ?>admin/commandes">Commandes</a></li>
                <?php if ($role === 'admin'): ?>
                    <li><a href="<?php echo APP_URL; ?>admin/utilisateurs">Utilisateurs</a></li>
                    <li><a href="<?php echo APP_URL; ?>admin/logs">Journal des actions</a></li>
                    <li><a href="<?php echo APP_URL; ?>admin/parametres">Paramètres</a></li>
                <?php endif; ?>
                <li><a href="<?php echo APP_URL; ?>admin/logout" style="color: #ef4444;">Déconnexion</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="admin-main-content">
            <header class="admin-header">
                <button class="admin-menu-toggle" id="adminMenuToggle" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1><?php echo isset($adminPageTitle) ? $adminPageTitle : 'Tableau de Bord'; ?></h1>
                <div style="color: var(--text-secondary);">
                    <?php $u = $_SESSION['admin_username'] ?? 'Utilisateur'; $r = $_SESSION['admin_role'] ?? 'editor'; ?>
                    <span id="currentUser"><?php echo htmlspecialchars($u); ?> (<?php echo htmlspecialchars($r); ?>)</span> | 
                    <a href="<?php echo APP_URL; ?>">Voir le site</a>
                </div>
            </header>

            <main class="admin-content">
                <?php if (isset($adminContentPath)): ?>
                    <?php include $adminContentPath; ?>
                <?php else: ?>
                    <p>Contenu d'administration</p>
                <?php endif; ?>
            </main>
        </div>
    </div>
</body>
</html>
