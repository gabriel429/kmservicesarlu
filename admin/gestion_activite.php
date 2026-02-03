<?php
$admin_title = 'Journal d’activité';
include __DIR__ . '/../includes/admin_header.php';
if (($_SESSION['user']['role'] ?? 'editor') === 'editor') {
    redirect(SITE_URL . '/admin/dashboard.php');
}
$pdo = getPDO();
$logs = $pdo->query('SELECT j.*, u.nom FROM journal_activite j LEFT JOIN utilisateurs u ON u.id = j.utilisateur_id ORDER BY j.date_action DESC LIMIT 200')->fetchAll();
?>
<h2 class="fw-bold mb-4">Journal d’activité</h2>
<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Utilisateur</th>
                <th>Action</th>
                <th>Contexte</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= e($log['nom'] ?? 'Système'); ?></td>
                    <td><?= e($log['action']); ?></td>
                    <td><?= e($log['contexte'] ?? ''); ?></td>
                    <td><?= e($log['date_action']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
