<?php
$page_title = 'KM SERVICES - Accueil';
include __DIR__ . '/includes/header.php';
?>
<section class="hero hero-carousel">
    <div id="homeCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="<?= SITE_URL ?>/assets/images/image_principale.jpeg" class="d-block w-100" alt="KM Services - Image principale">
            </div>
            <div class="carousel-item">
                <img src="<?= SITE_URL ?>/assets/images/construction.jpeg" class="d-block w-100" alt="KM Services - Construction">
            </div>
            <div class="carousel-item">
                <img src="<?= SITE_URL ?>/assets/images/forage.jpeg" class="d-block w-100" alt="KM Services - Forage">
            </div>
            <div class="carousel-item">
                <img src="<?= SITE_URL ?>/assets/images/materiels-construction.jpeg" class="d-block w-100" alt="KM Services - Matériels de construction">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Précédent</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Suivant</span>
        </button>
    </div>
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <div class="row g-5 align-items-center justify-content-center">
            <div class="col-lg-7 text-white">
                <h1 class="display-4 fw-bold">KM SERVICES SARLU</h1>
                <p class="lead">Entreprise dynamique et polyvalente opérant dans le secteur du bâtiment et des travaux publics. Spécialisée dans la construction, la plomberie, l'électrification, la maintenance, le forage ainsi que la fourniture de matériels et matériaux de construction.</p>
                <div class="d-flex gap-3 mt-4 flex-wrap">
                    <a href="<?= SITE_URL ?>/devis.php" class="btn btn-primary">Demander un devis</a>
                    <a href="<?= SITE_URL ?>/projects.php" class="btn btn-outline-light">Voir nos projets</a>
                </div>
                <div class="row g-3 mt-4">
                    <div class="col-md-4">
                        <div class="card p-3 bg-transparent border-light text-white">
                            <h5 class="fw-bold">Qualité & sécurité</h5>
                            <p class="mb-0">Normes respectées et protocoles stricts sur chantier.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3 bg-transparent border-light text-white">
                            <h5 class="fw-bold">Solutions complètes</h5>
                            <p class="mb-0">De la conception à la réalisation, avec suivi rigoureux.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3 bg-transparent border-light text-white">
                            <h5 class="fw-bold">Innovation durable</h5>
                            <p class="mb-0">Technologies modernes et pratiques éco-responsables.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 services-showcase">
    <div class="container">
        <div class="text-center mb-5">
            <h6 class="text-primary fw-bold mb-2">Nos Services</h6>
            <h2 class="section-title fw-bold">Solutions complètes pour vos projets</h2>
        </div>
        <div class="row g-4">
            <?php
            try {
                $services = getPDO()->query('SELECT * FROM services ORDER BY display_order ASC, id ASC LIMIT 6')->fetchAll();
            } catch (PDOException $e) {
                $services = getPDO()->query('SELECT * FROM services ORDER BY id ASC LIMIT 6')->fetchAll();
            }
            foreach ($services as $service): ?>
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

<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-md-6">
                <h2 class="section-title">Une approche centrée sur la performance</h2>
                <p class="text-muted">Nous combinons expertise locale, rigueur opérationnelle et innovation pour livrer des projets fiables, durables et sécurisés.</p>
                <div class="row g-3 mt-3">
                    <div class="col-6">
                        <div class="card p-3 text-center">
                            <h3 class="fw-bold text-primary">+50</h3>
                            <p class="mb-0 text-muted">Projets réalisés</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card p-3 text-center">
                            <h3 class="fw-bold text-primary">98%</h3>
                            <p class="mb-0 text-muted">Clients satisfaits</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-0 overflow-hidden video-card">
                    <div class="ratio ratio-16x9">
                        <video class="w-100 h-100" controls poster="assets/images/video-poster.jpg">
                            <source src="assets/videos/publicite.mp4" type="video/mp4">
                            Votre navigateur ne supporte pas la lecture de vidéos.
                        </video>
                    </div>
                    <div class="video-play" aria-hidden="true">
                        <i class="fa-solid fa-play"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="section-title">Projets récents</h2>
                <p class="text-muted">Nos réalisations les plus récentes.</p>
            </div>
            <a href="<?= SITE_URL ?>/projects.php" class="btn btn-outline-primary">Voir tout</a>
        </div>
        <div class="row g-4">
            <?php
            $projects = [];
            try {
                $projects = getPDO()->query("SELECT * FROM projets ORDER BY date_creation DESC LIMIT 3")->fetchAll();
            } catch (PDOException $e) {
                error_log('Index projects section error: ' . $e->getMessage());
                $projects = [];
            }
            if (!$projects):
                $projects = [
                    ['titre' => 'Résidence Moderne', 'description' => 'Construction d’une résidence haut standing.'],
                    ['titre' => 'Réhabilitation Route Nationale', 'description' => 'Travaux de réhabilitation routière.'],
                    ['titre' => 'Entrepôt logistique', 'description' => 'Bâtiment industriel et stockage.'],
                ];
            endif;
            foreach ($projects as $project): ?>
                <div class="col-md-4">
                    <div class="card card-project p-4 h-100 d-flex flex-column">
                        <h5 class="fw-bold"><?= e($project['titre']); ?></h5>
                        <p class="text-muted project-desc-preview"><?= e($project['description']); ?></p>
                        <div class="mt-auto">
                            <?php $status = $project['statut'] ?? 'en cours'; ?>
                            <span class="badge badge-status text-white mb-2"><?= e(ucfirst(str_replace('_', ' ', $status))); ?></span>
                            <?php if (isset($project['id'])): ?>
                                <a href="<?= SITE_URL ?>/project_detail.php?id=<?= $project['id']; ?>" class="btn btn-sm btn-outline-primary w-100">Lire la suite</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container text-center">
        <h2 class="section-title">Besoin d’un partenaire fiable ?</h2>
        <p class="text-muted">Contactez KM SERVICES pour une étude gratuite.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?= SITE_URL ?>/contact.php" class="btn btn-primary">Nous contacter</a>
            <a href="<?= SITE_URL ?>/services.php" class="btn btn-outline-primary">Découvrir nos services</a>
        </div>
    </div>
</section>

<section class="py-4 partners-marquee">
    <div class="container">
        <div class="text-center mb-3">
            <h6 class="text-primary fw-bold mb-1">Nos partenaires</h6>
            <p class="text-muted mb-0">Ils nous font confiance.</p>
        </div>
    </div>
    <div class="container-fluid px-0">
        <div class="marquee">
            <?php
            $partners = read_partners();
            if (!$partners) {
                $partners = [
                    ['name' => 'KM SERVICES', 'logo' => 'assets/images/logoKMS.png', 'url' => '#'],
                ];
            }
            ?>
            <div class="marquee-track">
                <?php foreach ($partners as $partner): ?>
                    <?php $logo = $partner['logo'] ?? 'assets/images/logoKMS.png'; ?>
                    <?php $name = $partner['name'] ?? 'Partenaire'; ?>
                    <?php $url = $partner['url'] ?? '#'; ?>
                    <?php if (!empty($url) && $url !== '#'): ?>
                        <a href="<?= e($url); ?>" target="_blank" rel="noopener">
                            <img src="<?= SITE_URL ?>/<?= e($logo); ?>" alt="<?= e($name); ?>">
                        </a>
                    <?php else: ?>
                        <img src="<?= SITE_URL ?>/<?= e($logo); ?>" alt="<?= e($name); ?>">
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="marquee-track" aria-hidden="true">
                <?php foreach ($partners as $partner): ?>
                    <?php $logo = $partner['logo'] ?? 'assets/images/logoKMS.png'; ?>
                    <?php $name = $partner['name'] ?? 'Partenaire'; ?>
                    <?php $url = $partner['url'] ?? '#'; ?>
                    <?php if (!empty($url) && $url !== '#'): ?>
                        <a href="<?= e($url); ?>" target="_blank" rel="noopener">
                            <img src="<?= SITE_URL ?>/<?= e($logo); ?>" alt="<?= e($name); ?>">
                        </a>
                    <?php else: ?>
                        <img src="<?= SITE_URL ?>/<?= e($logo); ?>" alt="<?= e($name); ?>">
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
