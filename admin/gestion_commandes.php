<?php
$admin_title = 'Gestion commandes';
include __DIR__ . '/../includes/admin_header.php';

$pdo = getPDO();
$message = null;

if (is_post()) {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $message = 'Session expirée.';
    } elseif (!empty($_POST['update_status_id'])) {
        $stmt = $pdo->prepare('UPDATE commandes SET statut = ? WHERE id = ?');
        $stmt->execute([trim($_POST['statut']), (int) $_POST['update_status_id']]);
        $message = 'Statut mis à jour.';
        log_activity('Mise à jour commande', 'ID ' . (int) $_POST['update_status_id']);
    }
}

$orders = $pdo->query('SELECT c.*, p.nom_produit FROM commandes c JOIN produits p ON p.id = c.produit_id ORDER BY c.date_commande DESC')->fetchAll();
?>
<h2 class="fw-bold mb-4">Commandes WhatsApp</h2>
<?php if ($message): ?>
    <div class="alert alert-info"><?= e($message); ?></div>
<?php endif; ?>
<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Produit</th>
                <th>Client</th>
                <th>Quantité</th>
                <th>Statut</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order):
                $info = json_decode($order['client_info'], true) ?: [];
                ?>
                <tr>
                    <td><?= e($order['nom_produit']); ?></td>
                    <td><?= e($info['nom'] ?? ''); ?><br><small><?= e($info['telephone'] ?? ''); ?></small></td>
                    <td><?= e((string) ($info['quantite'] ?? 1)); ?></td>
                    <td><?= e($order['statut']); ?></td>
                    <td><?= e($order['date_commande']); ?></td>
                    <td>
                        <form method="post" class="d-flex gap-2">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                            <input type="hidden" name="update_status_id" value="<?= e($order['id']); ?>">
                            <select name="statut" class="form-select form-select-sm">
                                <option value="nouvelle" <?= $order['statut'] === 'nouvelle' ? 'selected' : '' ?>>Nouvelle</option>
                                <option value="en_traitement" <?= $order['statut'] === 'en_traitement' ? 'selected' : '' ?>>En traitement</option>
                                <option value="terminee" <?= $order['statut'] === 'terminee' ? 'selected' : '' ?>>Terminée</option>
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
