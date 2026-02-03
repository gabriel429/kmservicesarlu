<?php
$admin_title = 'Gestion devis';
include __DIR__ . '/../includes/admin_header.php';

$pdo = getPDO();
$message = null;

if (is_post()) {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $message = 'Session expirée.';
    } elseif (!empty($_POST['update_status_id'])) {
        $stmt = $pdo->prepare('UPDATE demandes_devis SET statut = ? WHERE id = ?');
        $stmt->execute([trim($_POST['statut']), (int) $_POST['update_status_id']]);
        $message = 'Statut mis à jour.';
        log_activity('Mise à jour devis', 'ID ' . (int) $_POST['update_status_id']);
    }
}

$devis = $pdo->query('SELECT d.*, s.nom_service FROM demandes_devis d LEFT JOIN services s ON s.id = d.service_id ORDER BY d.date_demande DESC')->fetchAll();
?>
<h2 class="fw-bold mb-4">Demandes de devis</h2>
<?php if ($message): ?>
    <div class="alert alert-info"><?= e($message); ?></div>
<?php endif; ?>
<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Client</th>
                <th>Service</th>
                <th>Description</th>
                <th>Statut</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($devis as $item):
                $info = json_decode($item['client_info'], true) ?: [];
                ?>
                <tr>
                    <td>
                        <?= e($info['nom'] ?? ''); ?><br>
                        <small><?= e($info['email'] ?? ''); ?> | <?= e($info['telephone'] ?? ''); ?></small>
                    </td>
                    <td><?= e($item['nom_service'] ?? ''); ?></td>
                    <td><?= e($item['description']); ?></td>
                    <td><?= e($item['statut']); ?></td>
                    <td><?= e($item['date_demande']); ?></td>
                    <td>
                        <form method="post" class="d-flex gap-2">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                            <input type="hidden" name="update_status_id" value="<?= e($item['id']); ?>">
                            <select name="statut" class="form-select form-select-sm">
                                <option value="nouvelle" <?= $item['statut'] === 'nouvelle' ? 'selected' : '' ?>>Nouvelle</option>
                                <option value="en_traitement" <?= $item['statut'] === 'en_traitement' ? 'selected' : '' ?>>En traitement</option>
                                <option value="terminee" <?= $item['statut'] === 'terminee' ? 'selected' : '' ?>>Terminée</option>
                            </select>
                            <button class="btn btn-sm btn-primary" type="submit">Mettre à jour</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
