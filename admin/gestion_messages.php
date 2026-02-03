<?php
$admin_title = 'Messages de contact - KM SERVICES';
include __DIR__ . '/../includes/admin_header.php';

$pdo = getPDO();
$userRole = $_SESSION['user']['role'] ?? 'editor';
$isEditor = $userRole === 'editor';

// Les éditeurs ne peuvent pas accéder à cette page
if ($isEditor) {
    redirect(SITE_URL . '/admin/dashboard.php');
}

// Supprimer un message
if (is_post() && !empty($_POST['delete_id'])) {
    if (csrf_verify($_POST['csrf_token'] ?? null)) {
        $stmt = $pdo->prepare('DELETE FROM messages_contact WHERE id = ?');
        $stmt->execute([(int) $_POST['delete_id']]);
        log_activity('Suppression message contact', 'ID ' . (int) $_POST['delete_id']);
    }
}

// Récupérer les messages
$messages = $pdo->query('SELECT * FROM messages_contact ORDER BY date_message DESC')->fetchAll();
?>

<h2 class="fw-bold mb-4">Messages de contact</h2>

<?php if (empty($messages)): ?>
    <div class="alert alert-info" role="alert">
        Aucun message de contact pour le moment.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                    <tr>
                        <td>
                            <strong><?= e($msg['nom']); ?></strong>
                        </td>
                        <td>
                            <a href="mailto:<?= e($msg['email']); ?>"><?= e($msg['email']); ?></a>
                        </td>
                        <td>
                            <?php if (!empty($msg['telephone'])): ?>
                                <a href="tel:<?= e($msg['telephone']); ?>"><?= e($msg['telephone']); ?></a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <p class="mb-0 text-muted" style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= e($msg['message']); ?>
                            </p>
                        </td>
                        <td>
                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($msg['date_message'])); ?></small>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#messageModal<?= $msg['id']; ?>">
                                <i class="fa-solid fa-eye"></i> Voir
                            </button>
                            <form method="post" style="display: inline;">
                                <?= csrf_token_field(); ?>
                                <input type="hidden" name="delete_id" value="<?= $msg['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce message ?')">
                                    <i class="fa-solid fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal pour voir le message complet -->
                    <div class="modal fade" id="messageModal<?= $msg['id']; ?>" tabindex="-1" aria-labelledby="messageModalLabel<?= $msg['id']; ?>">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="messageModalLabel<?= $msg['id']; ?>">Message de <?= e($msg['nom']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>De:</strong> <?= e($msg['nom']); ?></p>
                                    <p><strong>Email:</strong> <a href="mailto:<?= e($msg['email']); ?>"><?= e($msg['email']); ?></a></p>
                                    <?php if (!empty($msg['telephone'])): ?>
                                        <p><strong>Téléphone:</strong> <a href="tel:<?= e($msg['telephone']); ?>"><?= e($msg['telephone']); ?></a></p>
                                    <?php endif; ?>
                                    <p><strong>Date:</strong> <?= date('d/m/Y à H:i', strtotime($msg['date_message'])); ?></p>
                                    <hr>
                                    <p><strong>Message:</strong></p>
                                    <p><?= nl2br(e($msg['message'])); ?></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    <a href="mailto:<?= e($msg['email']); ?>" class="btn btn-primary">
                                        <i class="fa-solid fa-reply"></i> Répondre
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
