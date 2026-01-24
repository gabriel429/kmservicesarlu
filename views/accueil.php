<!-- Accueil -->
<section class="hero">
    <div class="hero-content">
        <h1>KM Services</h1>
        <p>KM SERVICES est votre partenaire de confiance pour tous vos projets de construction et d’infrastructures. Spécialisée dans la construction, la plomberie, l’électrification, la maintenance, le forage ainsi que la fourniture de matériels et matériaux de construction, notre entreprise met son expertise, son professionnalisme et son sens du détail au service de réalisations durables, modernes et conformes aux normes.</p>
        <div class="hero-buttons">
            <a href="<?php echo APP_URL; ?>contact" class="btn btn-primary">Demander un Devis</a>
            <a href="<?php echo APP_URL; ?>projets" class="btn btn-secondary">Voir Nos Projets</a>
        </div>
    </div>
</section>

<!-- Services -->
<section class="services" id="services">
    <div class="container">
        <h2>Nos Services</h2>
        <div class="services-grid">
            <div class="service-card">
                <i class="fas fa-hammer"></i>
                <h3>Construction</h3>
                <p>Services complets de construction et infrastructure. Nous réalisons vos projets avec expertise et professionnalisme.</p>
                <a href="<?php echo APP_URL; ?>services">En savoir plus</a>
            </div>
            <div class="service-card">
                <i class="fas fa-drill"></i>
                <h3>Forage</h3>
                <p>Forages de puits et services spécialisés. Solutions adaptées à vos besoins avec équipements modernes.</p>
                <a href="<?php echo APP_URL; ?>services">En savoir plus</a>
            </div>
            <div class="service-card">
                <i class="fas fa-pipe"></i>
                <h3>Plomberie</h3>
                <p>Installation, réparation et entretien des systèmes de plomberie. Travail conforme aux normes de sécurité et qualité.</p>
                <a href="<?php echo APP_URL; ?>services">En savoir plus</a>
            </div>
            <div class="service-card">
                <i class="fas fa-paint-brush"></i>
                <h3>Peinture</h3>
                <p>Services complets de peinture intérieure et extérieure avec des finitions de qualité professionnelle.</p>
                <a href="<?php echo APP_URL; ?>services">En savoir plus</a>
            </div>
            <div class="service-card">
                <i class="fas fa-bolt"></i>
                <h3>Électrification</h3>
                <p>Solutions complètes en électricité pour l'installation, la maintenance et l'upgrade des systèmes électriques.</p>
                <a href="<?php echo APP_URL; ?>services">En savoir plus</a>
            </div>
            <div class="service-card">
                <i class="fas fa-cube"></i>
                <h3>Matériels</h3>
                <p>Vente de matériels de construction de qualité. Large gamme de produits au meilleur prix.</p>
                <a href="<?php echo APP_URL; ?>boutique">Voir la boutique</a>
            </div>
        </div>
    </div>
</section>

<!-- Projets Récents -->
<section class="featured-projects">
    <div class="container">
        <h2>Nos Projets Phares</h2>
        <div class="projects-grid">
            <?php if (!empty($projects)): ?>
                <?php foreach (array_slice($projects, 0, 3) as $project): ?>
                    <div class="project-card">
                        <div class="project-image">
                            <?php if ($project['image_principale']): ?>
                                <img src="<?php echo APP_URL; ?>uploads/<?php echo htmlspecialchars($project['image_principale']); ?>" alt="<?php echo htmlspecialchars($project['titre']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="project-info">
                            <h3><?php echo htmlspecialchars($project['titre']); ?></h3>
                            <p><?php echo substr($project['description'], 0, 100) . '...'; ?></p>
                            <a href="<?php echo APP_URL; ?>projets/<?php echo htmlspecialchars($project['slug']); ?>" class="btn-link">Voir le projet</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="text-center">
            <a href="<?php echo APP_URL; ?>projets" class="btn btn-primary">Voir Tous les Projets</a>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta">
    <div class="container">
        <h2>Prêt à Démarrer Votre Projet?</h2>
        <p>Contactez-nous dès maintenant pour une consultation gratuite</p>
        <a href="<?php echo APP_URL; ?>contact" class="btn btn-large">Nous Contacter</a>
    </div>
</section>
