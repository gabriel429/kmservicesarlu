<?php
$page_title = 'Projets - KM SERVICES';
include __DIR__ . '/includes/header.php';
$projects = getPDO()->query("SELECT p.*, s.nom_service FROM projets p LEFT JOIN services s ON s.id = p.service_id ORDER BY p.date_creation DESC")->fetchAll();
?>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Nos projets</h2>
            <p class="text-muted">Découvrez nos réalisations.</p>
        </div>
        <div class="row g-4">
            <?php if (!$projects): ?>
                <div class="col-12 text-center text-muted">Aucun projet enregistré pour le moment.</div>
            <?php endif; ?>
            <?php foreach ($projects as $project): ?>
                <div class="col-md-4">
                    <a href="<?= SITE_URL ?>/project_detail.php?id=<?= e($project['id']); ?>" class="text-decoration-none">
                        <div class="card card-project p-4 h-100">
                            <?php
                            $images = [];
                            if (!empty($project['images'])) {
                                $decoded = json_decode($project['images'], true);
                                if (is_array($decoded)) {
                                    $images = $decoded;
                                }
                            }
                            ?>
                            <?php if (!empty($images[0])): ?>
                                <img src="<?= e($images[0]); ?>" alt="<?= e($project['titre']); ?>" class="img-fluid rounded mb-3" style="height: 180px; object-fit: cover; width: 100%;">
                            <?php endif; ?>
                            <h5 class="fw-bold text-dark"><?= e($project['titre']); ?></h5>
                            <p class="text-muted"><?= e(mb_substr($project['description'], 0, 100)); ?>...</p>
                            <span class="badge badge-status text-white"><?= e($project['statut']); ?></span>
                            <?php if (!empty($project['nom_service'])): ?>
                                <small class="text-muted mt-2 d-block">Service: <?= e($project['nom_service']); ?></small>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
