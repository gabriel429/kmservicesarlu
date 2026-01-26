<!-- Détail Projet -->
<section class="project-detail-page">
    <div class="container">
        <?php 
        // Charger le projet depuis les données passées
        $project = $_GET['_project'] ?? null;
        $projects = $_GET['_projects'] ?? [];
        
        if ($project): ?>
            <h1><?php echo htmlspecialchars($project['titre']); ?></h1>
            
            <div class="project-meta">
                <span class="meta-item">
                    <i class="fas fa-map-marker-alt"></i> 
                    <?php echo htmlspecialchars($project['localisation'] ?? 'Non spécifié'); ?>
                </span>
                <span class="meta-item">
                    <i class="fas fa-calendar"></i> 
                    <?php echo (!empty($project['date_fin'])) ? date('F Y', strtotime($project['date_fin'])) : 'En cours'; ?>
                </span>
                <span class="meta-item">
                    <i class="fas fa-check-circle"></i> 
                    <?php echo ($project['statut'] === 'realise' || $project['statut'] === 'termine') ? 'Réalisé' : 'En cours'; ?>
                </span>
            </div>
            
            <div class="project-detail-content">
                <div class="project-main">
                    <?php if (!empty($project['image_principale'])): ?>
                        <div class="project-main-image">
                            <?php $rel = $project['image_principale']; $isUrl = is_string($rel) && str_starts_with($rel, 'http'); ?>
                            <?php if ($isUrl): ?>
                                <img src="/img?url=<?php echo urlencode($rel); ?>&w=1200&h=400&q=85&format=webp" alt="<?php echo htmlspecialchars($project['titre']); ?>" width="1200" height="400"
                                     srcset="/img?url=<?php echo urlencode($rel); ?>&w=600&h=400&q=85&format=webp 600w, /img?url=<?php echo urlencode($rel); ?>&w=1200&h=400&q=85&format=webp 1200w"
                                     sizes="(max-width: 768px) 100vw, 1200px">
                            <?php else: ?>
                                <img src="/img?p=uploads/projects/<?php echo htmlspecialchars($rel); ?>&w=1200&h=400&q=85&format=webp" alt="<?php echo htmlspecialchars($project['titre']); ?>" width="1200" height="400"
                                     srcset="/img?p=uploads/projects/<?php echo htmlspecialchars($rel); ?>&w=600&h=400&q=85&format=webp 600w, /img?p=uploads/projects/<?php echo htmlspecialchars($rel); ?>&w=1200&h=400&q=85&format=webp 1200w"
                                     sizes="(max-width: 768px) 100vw, 1200px">
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="project-main-image">
                            <img src="<?php echo ASSET_URL; ?>assets/images/placeholder_project.svg" alt="Image indisponible">
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($project['video_url'])): ?>
                        <div class="project-video">
                            <iframe width="100%" height="400" src="<?php echo htmlspecialchars($project['video_url']); ?>" frameborder="0" allowfullscreen></iframe>
                        </div>
                    <?php endif; ?>
                    
                    <div class="project-description">
                        <h2>Description</h2>
                        <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                    </div>
                </div>
                
                <aside class="project-sidebar">
                    <div class="project-info-box">
                        <h3>Détails du Projet</h3>
                        
                        <?php if (!empty($project['client'])): ?>
                            <div class="info-item">
                                <span class="label">Client:</span>
                                <span class="value"><?php echo htmlspecialchars($project['client']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($project['date_debut']) || !empty($project['date_fin'])): ?>
                            <div class="info-item">
                                <span class="label">Période:</span>
                                <span class="value">
                                    <?php echo !empty($project['date_debut']) ? date('M Y', strtotime($project['date_debut'])) : ''; ?>
                                    <?php echo (!empty($project['date_debut']) && !empty($project['date_fin'])) ? ' - ' : ''; ?>
                                    <?php echo !empty($project['date_fin']) ? date('M Y', strtotime($project['date_fin'])) : ''; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($project['budget']) && $project['budget'] !== null && $project['budget'] !== ''): ?>
                            <div class="info-item">
                                <span class="label">Budget:</span>
                                <span class="value">$<?php echo number_format((float)$project['budget'], 2); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <span class="label">Statut:</span>
                            <span class="value">
                                <span class="status-badge <?php echo !empty($project['statut']) ? $project['statut'] : 'en_cours'; ?>">
                                    <?php echo (!empty($project['statut']) && ($project['statut'] === 'realise' || $project['statut'] === 'termine')) ? 'Réalisé' : 'En cours'; ?>
                                </span>
                            </span>
                        </div>
                    </div>
                    
                    <div class="cta-box">
                        <a href="<?php echo APP_URL; ?>contact" class="btn btn-primary btn-block">
                            <i class="fas fa-envelope"></i> Demander un Devis
                        </a>
                    </div>
                </aside>
            </div>
            
            <div class="project-gallery">
                <h2>Galerie Photos</h2>
                <div class="gallery-grid">
                    <?php
                    try {
                        if (isset($project['id'])) {
                            if (!class_exists('MySQLCore')) {
                                require_once dirname(__DIR__) . '/app/MySQL.php';
                            }
                            $images = MySQLCore::fetchAll(
                                "SELECT image_path, alt_text FROM project_images WHERE project_id = ? ORDER BY ordre ASC, id ASC",
                                [$project['id']]
                            );
                        } else {
                            $images = [];
                        }
                    } catch (Exception $e) {
                        $images = [];
                    }
                    if (!empty($images)):
                        foreach ($images as $image):
                    ?>
                        <div class="gallery-item">
                               <?php $rel = $image['image_path']; $isUrl = is_string($rel) && str_starts_with($rel, 'http'); ?>
                               <?php if ($isUrl): ?>
                                  <img src="/img?url=<?php echo urlencode($rel); ?>&w=400&h=200&q=85&format=webp" alt="<?php echo htmlspecialchars($image['alt_text'] ?? ''); ?>" loading="lazy" width="400" height="200"
                                      srcset="/img?url=<?php echo urlencode($rel); ?>&w=200&h=200&q=85&format=webp 200w, /img?url=<?php echo urlencode($rel); ?>&w=400&h=200&q=85&format=webp 400w"
                                      sizes="(max-width: 768px) 100vw, 400px">
                               <?php else: ?>
                                  <img src="/img?p=uploads/projects/<?php echo htmlspecialchars($rel); ?>&w=400&h=200&q=85&format=webp" alt="<?php echo htmlspecialchars($image['alt_text'] ?? ''); ?>" loading="lazy" width="400" height="200"
                                      srcset="/img?p=uploads/projects/<?php echo htmlspecialchars($rel); ?>&w=200&h=200&q=85&format=webp 200w, /img?p=uploads/projects/<?php echo htmlspecialchars($rel); ?>&w=400&h=200&q=85&format=webp 400w"
                                      sizes="(max-width: 768px) 100vw, 400px">
                               <?php endif; ?>
                        </div>
                    <?php endforeach; else: ?>
                        <p>Aucune photo supplémentaire disponible.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="related-projects">
                <h2>Projets Similaires</h2>
                <div class="projects-grid">
                    <?php if (!empty($projects)): ?>
                        <?php $i = 0; foreach ($projects as $p): 
                            if ($p['id'] !== $project['id'] && $i < 3):
                                $i++;
                        ?>
                            <div class="project-card">
                                <div class="project-image">
                                    <?php if (!empty($p['image_principale'])): ?>
                                        <img src="/img?p=uploads/projects/<?php echo htmlspecialchars($p['image_principale']); ?>&w=400&h=200&q=85&format=webp" alt="<?php echo htmlspecialchars($p['titre']); ?>" loading="lazy" width="400" height="200"
                                             srcset="/img?p=uploads/projects/<?php echo htmlspecialchars($p['image_principale']); ?>&w=200&h=200&q=85&format=webp 200w, /img?p=uploads/projects/<?php echo htmlspecialchars($p['image_principale']); ?>&w=400&h=200&q=85&format=webp 400w"
                                             sizes="(max-width: 768px) 100vw, 400px">
                                    <?php else: ?>
                                        <img src="<?php echo ASSET_URL; ?>assets/images/placeholder_project.svg" alt="Image indisponible">
                                    <?php endif; ?>
                                </div>
                                <div class="project-content">
                                    <h3><?php echo htmlspecialchars($p['titre']); ?></h3>
                                    <p><?php echo substr($p['description'], 0, 100) . '...'; ?></p>
                                    <a href="<?php echo APP_URL; ?>projets/<?php echo htmlspecialchars($p['slug']); ?>" class="btn-link">Voir le projet</a>
                                </div>
                            </div>
                        <?php endif; endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <p>Projet non trouvé.</p>
        <?php endif; ?>
    </div>
</section>

<style>
.project-meta {
    display: flex;
    gap: 2rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.meta-item {
    color: var(--text-secondary);
    font-size: 0.95rem;
}

.meta-item i {
    color: var(--secondary-color);
    margin-right: 0.5rem;
}

.project-detail-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-bottom: 3rem;
}

.project-main-image {
    width: 100%;
    border-radius: 0.5rem;
    overflow: hidden;
    margin-bottom: 2rem;
}

.project-main-image img {
    width: 100%;
    height: auto;
    display: block;
}

.project-video {
    margin-bottom: 2rem;
    border-radius: 0.5rem;
    overflow: hidden;
}

.project-description {
    background-color: var(--light-bg);
    padding: 2rem;
    border-radius: 0.5rem;
}

.project-description h2 {
    text-align: left;
    margin-top: 0;
}

.project-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    height: fit-content;
}

.project-info-box {
    background-color: var(--light-bg);
    padding: 1.5rem;
    border-radius: 0.5rem;
}

.project-info-box h3 {
    text-align: left;
    margin-bottom: 1rem;
}

.info-item {
    display: grid;
    grid-template-columns: 120px 1fr;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.info-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.info-item .label {
    font-weight: 600;
    color: var(--primary-color);
}

.info-item .value {
    color: var(--text-secondary);
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 0.25rem;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-badge.realise, .status-badge.termine {
    background-color: #d1fae5;
    color: #065f46;
}

.status-badge.en_cours {
    background-color: #dbeafe;
    color: #0c4a6e;
}

.cta-box {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    padding: 1.5rem;
    border-radius: 0.5rem;
    text-align: center;
}

.cta-box .btn {
    width: 100%;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 3rem;
}

.gallery-item {
    border-radius: 0.5rem;
    overflow: hidden;
    cursor: pointer;
}

.gallery-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: var(--transition);
}

.gallery-item:hover img {
    transform: scale(1.05);
}

@media (max-width: 768px) {
    .project-detail-content {
        grid-template-columns: 1fr;
    }
    
    .project-sidebar {
        height: auto;
    }
    
    .info-item {
        grid-template-columns: 100px 1fr;
    }
}
</style>
