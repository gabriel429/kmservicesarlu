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
];
$userRole = $_SESSION['user']['role'] ?? 'editor';
$isEditor = $userRole === 'editor';
$partnersCount = $isEditor ? 0 : count(read_partners());
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
                <h6 class="text-muted">Partenaires</h6>
                <h3 class="fw-bold"><?= $partnersCount; ?></h3>
                <a href="<?= SITE_URL ?>/admin/gestion_partenaires.php" class="btn btn-sm btn-outline-primary mt-2">Gérer</a>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
