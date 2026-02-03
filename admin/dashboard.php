<?php
$admin_title = 'Dashboard - KM SERVICES';
include __DIR__ . '/../includes/admin_header.php';

$pdo = getPDO();
$stats = [
    'services' => (int) $pdo->query('SELECT COUNT(*) FROM services')->fetchColumn(),
    'produits' => (int) $pdo->query('SELECT COUNT(*) FROM produits')->fetchColumn(),
    'projets' => (int) $pdo->query('SELECT COUNT(*) FROM projets')->fetchColumn(),
    'commandes' => (int) $pdo->query('SELECT COUNT(*) FROM commandes')->fetchColumn(),
    'devis' => (int) $pdo->query('SELECT COUNT(*) FROM demandes_devis')->fetchColumn(),
    'messages' => (int) $pdo->query('SELECT COUNT(*) FROM messages_contact')->fetchColumn(),
];
$userRole = $_SESSION['user']['role'] ?? 'editor';
$isEditor = $userRole === 'editor';
$partnersCount = $isEditor ? 0 : count(read_partners());

// Récupérer les messages récents (seulement pour les admins)
$recentMessages = [];
if (!$isEditor) {
    $recentMessages = $pdo->query('SELECT * FROM messages_contact ORDER BY date_message DESC LIMIT 5')->fetchAll();
}
?>
<h2 class="fw-bold mb-4">Tableau de bord</h2>
<div class="row g-4">
    <div class="col-md-3">
        <div class="card p-3">
            <h6 class="text-muted">Services</h6>
            <h3 class="fw-bold"><?= $stats['services']; ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <h6 class="text-muted">Produits</h6>
            <h3 class="fw-bold"><?= $stats['produits']; ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <h6 class="text-muted">Projets</h6>
            <h3 class="fw-bold"><?= $stats['projets']; ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <h6 class="text-muted">Commandes</h6>
            <h3 class="fw-bold"><?= $stats['commandes']; ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <h6 class="text-muted">Demandes devis</h6>
            <h3 class="fw-bold"><?= $stats['devis']; ?></h3>
        </div>
    </div>
    <?php if (!$isEditor): ?>
        <div class="col-md-3">
            <div class="card p-3">
                <h6 class="text-muted">Messages</h6>
                <h3 class="fw-bold"><?= $stats['messages']; ?></h3>
                <a href="<?= SITE_URL ?>/admin/gestion_messages.php" class="btn btn-sm btn-outline-primary mt-2">Consulter</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <h6 class="text-muted">Partenaires</h6>
                <h3 class="fw-bold"><?= $partnersCount; ?></h3>
                <a href="<?= SITE_URL ?>/admin/gestion_partenaires.php" class="btn btn-sm btn-outline-primary mt-2">Gérer</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!$isEditor && !empty($recentMessages)): ?>
<div class="mt-5">
    <h3 class="fw-bold mb-4">Messages récents</h3>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Message</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentMessages as $msg): ?>
                    <tr>
                        <td><strong><?= e($msg['nom']); ?></strong></td>
                        <td><a href="mailto:<?= e($msg['email']); ?>"><?= e($msg['email']); ?></a></td>
                        <td><a href="tel:<?= e($msg['telephone']); ?>"><?= e($msg['telephone']); ?></a></td>
                        <td>
                            <p class="mb-0 text-muted" style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= e($msg['message']); ?>
                            </p>
                        </td>
                        <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime($msg['date_message'])); ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <a href="<?= SITE_URL ?>/admin/gestion_messages.php" class="btn btn-primary">Voir tous les messages</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
