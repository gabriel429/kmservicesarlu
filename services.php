<?php
$page_title = 'Services - KM SERVICES';
include __DIR__ . '/includes/header.php';

$services = [];
try {
    // Try to fetch services with ordering
    $stmt = getPDO()->query('SELECT * FROM services ORDER BY display_order ASC, id ASC');
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    // Fallback: try without display_order column
    try {
        $stmt = getPDO()->query('SELECT * FROM services ORDER BY id ASC');
        $services = $stmt->fetchAll();
    } catch (PDOException $e2) {
        // Log error for debugging (log to a file instead of displaying)
        error_log('Services page error: ' . $e2->getMessage());
        $services = [];
    }
}
?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-md-7">
                <h2 class="section-title">Solutions intégrées pour vos chantiers</h2>
                <p class="text-muted">Du conseil à l’exécution, nous déployons des équipes qualifiées pour garantir des résultats durables.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <span class="badge bg-dark">Qualité</span>
                    <span class="badge bg-dark">Sécurité</span>
                    <span class="badge bg-dark">Innovation</span>
                    <span class="badge bg-dark">Durabilité</span>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card p-4">
                    <h6 class="fw-bold">Besoin d’un devis rapide ?</h6>
                    <p class="text-muted">Décrivez votre projet et recevez une proposition personnalisée.</p>
                    <a href="<?= SITE_URL ?>/devis.php" class="btn btn-primary">Demander un devis</a>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="py-5 services-gallery">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Nos services</h2>
            <p class="text-muted">Des prestations adaptées à vos besoins.</p>
        </div>
        <div class="row g-4 mb-5">
            <div class="col-md-7">
                <h5 class="fw-bold">Pourquoi choisir KM SERVICES</h5>
                <p>Nous intégrons les meilleures pratiques du secteur pour garantir la qualité, la sécurité et la durabilité de vos projets.</p>
                <ul class="list-unstyled">
                    <li><i class="fa-solid fa-check text-primary"></i> Qualité : respect des normes et finitions soignées</li>
                    <li><i class="fa-solid fa-check text-primary"></i> Sécurité : protocoles stricts sur chantier</li>
                    <li><i class="fa-solid fa-check text-primary"></i> Innovation : solutions modernes et écoénergétiques</li>
                    <li><i class="fa-solid fa-check text-primary"></i> Engagement environnemental : pratiques durables</li>
                </ul>
            </div>
            <div class="col-md-5">
                <div class="card p-4 h-100">
                    <h6 class="fw-bold">Notre approche</h6>
                    <p class="text-muted">Une équipe d’experts qualifiés, formés régulièrement, et des partenaires de confiance pour livrer des projets performants.</p>
                </div>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($services as $service): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="service-card-showcase service-card-with-desc">
                        <?php
                        $iconValue = $service['icone'] ?? '';
                        $isImage = $iconValue && preg_match('/\.(png|jpe?g|webp|svg)$/i', $iconValue);
                        ?>
                        <?php if ($isImage): ?>
                            <img src="<?= e($iconValue); ?>" alt="<?= e($service['nom_service']); ?>" class="service-card-bg">
                        <?php else: ?>
                            <div class="service-card-bg-placeholder"><i class="<?= e($iconValue ?: 'fa-solid fa-briefcase'); ?> fa-3x text-primary"></i></div>
                        <?php endif; ?>
                        <div class="service-card-overlay"></div>
                        <div class="service-card-content">
                            <h5 class="service-card-title"><?= e($service['nom_service']); ?></h5>
                            <div class="service-card-plus">+</div>
                        </div>
                        <div class="service-card-desc">
                            <p><?= e($service['description']); ?></p>
                            <?php if (stripos($service['nom_service'], 'fourniture') !== false || stripos($service['nom_service'], 'matériaux') !== false): ?>
                                <a href="<?= SITE_URL ?>/products.php" class="btn btn-sm btn-outline-light">Voir la boutique</a>
                            <?php else: ?>
                                <a href="<?= SITE_URL ?>/devis.php" class="btn btn-sm btn-outline-light">Demander un devis</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
