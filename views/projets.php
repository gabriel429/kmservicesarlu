<?php
// Assurer le chargement des projets pour l'affichage visiteur
if (!isset($projects) || empty($projects)) {
    if (!class_exists('MySQLCore')) {
        require_once dirname(__DIR__) . '/app/MySQL.php';
    }
    try {
        $projects = MySQLCore::fetchAll(
            "SELECT id, titre, slug, description, localisation, image_principale, statut FROM projects ORDER BY ordre ASC, created_at DESC LIMIT 100"
        );
    } catch (Exception $e) {
        $projects = [];
    }
}
?>
<!-- Page Projets -->
<section class="projects-page">
    <div class="container">
        <h1>Nos Projets</h1>
        
        <div class="projects-filters">
            <button class="filter-btn active" data-filter="tous">Tous</button>
            <button class="filter-btn" data-filter="termine">Réalisés</button>
            <button class="filter-btn" data-filter="en_cours">En Cours</button>
        </div>
        
        <div class="projects-grid">
            <?php if (!empty($projects)): ?>
                <?php foreach ($projects as $project): ?>
                    <?php
                        // Récupérer l'image principale depuis project_images si disponible (priorité)
                        $projectImage = null;
                        try {
                            if (!class_exists('MySQLCore')) {
                                require_once dirname(__DIR__) . '/app/MySQL.php';
                            }
                            $first = MySQLCore::fetch("SELECT image_path FROM project_images WHERE project_id = ? ORDER BY ordre ASC, id ASC LIMIT 1", [$project['id']]);
                            if ($first && !empty($first['image_path'])) {
                                $projectImage = $first['image_path'];
                            } else {
                                $projectImage = $project['image_principale'] ?? null;
                            }
                        } catch (Throwable $e) {
                            $projectImage = $project['image_principale'] ?? null;
                        }
                    ?>
                    <div class="project-card" data-filter="<?php echo !empty($project['statut']) ? $project['statut'] : 'en_cours'; ?>">
                        <div class="project-image<?php echo empty($projectImage) ? ' placeholder' : ''; ?>">
                            <?php if (!empty($projectImage)): ?>
                                <?php echo renderImage($projectImage, $project['titre'], '400', '200', '', 'lazy'); ?>
                            <?php else: ?>
                                  <img src="<?php echo ASSET_URL; ?>assets/images/placeholder_project.svg" alt="Image indisponible" />
                            <?php endif; ?>
                            <span class="project-status"><?php echo ($project['statut'] === 'realise' || $project['statut'] === 'termine') ? 'Réalisé' : 'En Cours'; ?></span>
                        </div>
                        <div class="project-content">
                            <h3><?php echo htmlspecialchars($project['titre']); ?></h3>
                            <p class="project-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($project['localisation'] ?? 'Localisation non spécifiée'); ?></p>
                            <p><?php echo substr($project['description'], 0, 120) . '...'; ?></p>
                            <a href="<?php echo APP_URL; ?>projets/<?php echo htmlspecialchars($project['slug']); ?>" class="btn-link">Voir le projet</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-projects">Aucun projet disponible pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const filter = this.dataset.filter;
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        document.querySelectorAll('.project-card').forEach(card => {
            if (filter === 'tous' || card.dataset.filter === filter) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>
