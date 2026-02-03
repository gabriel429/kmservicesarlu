<?php
$page_title = 'Détail du projet - KM SERVICES';
include __DIR__ . '/includes/header.php';

if (empty($_GET['id'])) {
    redirect(SITE_URL . '/projects.php');
}

$stmt = getPDO()->prepare("SELECT p.*, s.nom_service FROM projets p LEFT JOIN services s ON s.id = p.service_id WHERE p.id = ?");
$stmt->execute([(int) $_GET['id']]);
$project = $stmt->fetch();

if (!$project) {
    redirect(SITE_URL . '/projects.php');
}

$images = [];
if (!empty($project['images'])) {
    $decoded = json_decode($project['images'], true);
    if (is_array($decoded)) {
        $images = $decoded;
    }
}
?>
<section class="py-5">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/projects.php">Projets</a></li>
                <li class="breadcrumb-item active"><?= e($project['titre']); ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h1 class="fw-bold mb-0"><?= e($project['titre']); ?></h1>
                        <span class="badge badge-status text-white"><?= e($project['statut']); ?></span>
                    </div>
                    <?php if (!empty($project['nom_service'])): ?>
                        <p class="text-primary fw-bold mb-3"><i class="fa-solid fa-briefcase"></i> <?= e($project['nom_service']); ?></p>
                    <?php endif; ?>
                    <div class="project-description">
                        <h5 class="fw-bold mb-3">Description du projet</h5>
                        <p class="text-muted" style="white-space: pre-line;"><?= e($project['description']); ?></p>
                    </div>
                </div>

                <?php if (!empty($images)): ?>
                    <div class="card p-4">
                        <h5 class="fw-bold mb-3">Galerie photos</h5>
                        <div class="row g-3">
                            <?php foreach ($images as $index => $image): ?>
                                <div class="col-md-6">
                                    <a href="<?= e($image); ?>" data-lightbox="project-gallery" data-title="<?= e($project['titre']); ?>">
                                        <img src="<?= e($image); ?>" alt="<?= e($project['titre']); ?> - Image <?= $index + 1; ?>" class="img-fluid rounded project-gallery-img">
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="card p-4 mb-4">
                    <h5 class="fw-bold mb-3">Informations</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><strong>Statut :</strong> <span class="text-capitalize"><?= e(str_replace('_', ' ', $project['statut'])); ?></span></li>
                        <li class="mb-2"><strong>Service :</strong> <?= e($project['nom_service'] ?? 'Non spécifié'); ?></li>
                        <li class="mb-2"><strong>Date :</strong> <?= date('d/m/Y', strtotime($project['date_creation'])); ?></li>
                    </ul>
                </div>

                <div class="card p-4" style="background: linear-gradient(135deg, var(--primary) 0%, #ff9a3c 100%);">
                    <h5 class="fw-bold text-white mb-3">Vous avez un projet similaire ?</h5>
                    <p class="text-white mb-3">Contactez-nous pour un devis personnalisé</p>
                    <a href="<?= SITE_URL ?>/devis.php" class="btn btn-light w-100">Demander un devis</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
