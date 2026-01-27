<?php
/**
 * Tableau de Bord Admin
 */
if (!class_exists('MySQLCore')) {
    require_once dirname(__DIR__, 2) . '/app/MySQL.php';
}
?>

<div class="stats-container">
    <?php
    try {
        $projectCount = MySQLCore::fetch("SELECT COUNT(*) as count FROM projects");
        $productCount = MySQLCore::fetch("SELECT COUNT(*) as count FROM products");
        $contactCount = MySQLCore::fetch("SELECT COUNT(*) as count FROM contacts WHERE statut = 'nouveau'");
        $forageCount = MySQLCore::fetch("SELECT COUNT(*) as count FROM drilling_requests WHERE statut = 'nouveau'");
        $orderCount = MySQLCore::fetch("SELECT COUNT(*) as count FROM orders WHERE statut = 'nouvelle'");
        
        // Créer la table si elle n'existe pas
        try {
            MySQLCore::execute(
                "CREATE TABLE IF NOT EXISTS quote_requests (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    numero_devis VARCHAR(50) UNIQUE NOT NULL,
                    nom VARCHAR(150) NOT NULL,
                    email VARCHAR(150) NOT NULL,
                    telephone VARCHAR(20) NOT NULL,
                    localisation VARCHAR(255),
                    service VARCHAR(100),
                    type_service VARCHAR(100),
                    description LONGTEXT,
                    delai_souhaite VARCHAR(100),
                    budget_estime DECIMAL(12, 2),
                    document_joint VARCHAR(255),
                    statut ENUM('nouveau', 'en_attente', 'contacte', 'accepte', 'refuse') DEFAULT 'nouveau',
                    lu TINYINT DEFAULT 0,
                    notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    treated_by INT DEFAULT NULL,
                    INDEX idx_statut (statut),
                    INDEX idx_created_at (created_at),
                    FOREIGN KEY (treated_by) REFERENCES users(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        } catch (Throwable $te) {
            // Table exists
        }
        $quoteCount = ['count' => 0];
        try {
            $quoteCount = MySQLCore::fetch("SELECT COUNT(*) as count FROM quote_requests WHERE statut = 'nouveau'");
        } catch (Throwable $qe) {
            // Table may not exist yet, use default
        }
    } catch (Exception $e) {
        $projectCount = ['count' => 0];
        $productCount = ['count' => 0];
        $contactCount = ['count' => 0];
        $forageCount = ['count' => 0];
        $orderCount = ['count' => 0];
        $quoteCount = ['count' => 0];
    }
    $role = $_SESSION['admin_role'] ?? 'editor';
    ?>
    <?php if ($role === 'admin'): ?>
        <div class="stat-card">
            <h3><?php echo $projectCount['count'] ?? 0; ?></h3>
            <p>Projets</p>
        </div>
    <?php endif; ?>
    
    <div class="stat-card">
        <h3><?php echo $productCount['count'] ?? 0; ?></h3>
        <p>Produits</p>
    </div>
    
    <div class="stat-card">
        <h3><?php echo $contactCount['count'] ?? 0; ?></h3>
        <p>Nouveaux Messages</p>
    </div>
    
    <div class="stat-card">
        <h3><?php echo $orderCount['count'] ?? 0; ?></h3>
        <p>Commandes en attente</p>
    </div>

    <div class="stat-card">
        <h3><?php echo $forageCount['count'] ?? 0; ?></h3>
        <p>Demandes de Forage</p>
    </div>

    <div class="stat-card">
        <h3><?php echo $quoteCount['count'] ?? 0; ?></h3>
        <p>Demandes de Devis</p>
    </div>
</div>

<div class="card">
    <h2>Bienvenue au Tableau de Bord Admin</h2>
    <p>Utilisez le menu latéral pour accéder aux différentes sections de gestion du site.</p>
    <ul>
        <?php if ($role === 'admin'): ?>
            <li><a href="<?php echo APP_URL; ?>admin/projets">Gérer les Projets</a></li>
        <?php endif; ?>
        <li><a href="<?php echo APP_URL; ?>admin/produits">Gérer les Produits</a></li>
        <li><a href="<?php echo APP_URL; ?>admin/contacts">Consulter les Messages</a></li>
        <li><a href="<?php echo APP_URL; ?>admin/commandes">Voir les Commandes</a></li>
        <li><a href="<?php echo APP_URL; ?>admin/devis">Consulter les Demandes de Devis</a></li>
    </ul>
</div>

<style>
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .card {
        margin-top: 1rem;
    }
    .form-row {
        display: flex;
        gap: 1rem;
        margin: 0.5rem 0;
    }
    .form-row input {
        flex: 1;
    }
</style>

<div class="card">
    <h3>Réinitialiser le mot de passe utilisateur</h3>
    <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin'): ?>
        <p>Saisissez le nom d'utilisateur (ou email) et le nouveau mot de passe.</p>
        <form id="resetPwdForm">
            <div class="form-row">
                <input type="text" name="identifier" placeholder="Username ou Email" required />
                <input type="password" name="password" placeholder="Nouveau mot de passe" required />
            </div>
            <button type="submit">Réinitialiser</button>
        </form>
        <p id="resetPwdMsg" style="margin-top:0.5rem;"></p>
    <?php else: ?>
        <p style="color:#b00;">Accès restreint: réservé aux administrateurs.</p>
    <?php endif; ?>
</div>

<script>
document.getElementById('resetPwdForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.currentTarget;
    const msg = document.getElementById('resetPwdMsg');
    msg.textContent = 'Traitement en cours...';
    try {
        const fd = new FormData(form);
        fd.append('action', 'reset_password');
        const res = await fetch('<?php echo APP_URL; ?>handlers/crud_users.php', {
            method: 'POST',
            body: fd
        });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch { data = { success:false, message:'Réponse invalide' }; }
        msg.textContent = data.success ? 'Succès: ' + (data.message || 'Mot de passe réinitialisé')
                                       : 'Erreur: ' + (data.message || 'Impossible de réinitialiser');
        if (data.success) form.reset();
    } catch (err) {
        msg.textContent = 'Erreur réseau: ' + err.message;
    }
});
</script>
