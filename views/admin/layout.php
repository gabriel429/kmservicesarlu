<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        .admin-main-content {
            grid-column: 2;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin-left: 0;
        }

        .admin-header {
            background-color: white;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
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
    </style>
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
