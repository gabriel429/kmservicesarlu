<?php
$admin_title = 'Gestion projets';
include __DIR__ . '/../includes/admin_header.php';

$pdo = getPDO();
$message = null;
$editProject = null;

if (!empty($_GET['edit_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM projets WHERE id = ?');
    $stmt->execute([(int) $_GET['edit_id']]);
    $editProject = $stmt->fetch();
}

if (is_post()) {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $message = 'Session expirée.';
    } elseif (isset($_POST['delete_id'])) {
        $stmt = $pdo->prepare('DELETE FROM projets WHERE id = ?');
        $stmt->execute([(int) $_POST['delete_id']]);
        $message = 'Projet supprimé.';
        log_activity('Suppression projet', 'ID ' . (int) $_POST['delete_id']);
    } elseif (!empty($_POST['update_id'])) {
        $images = [];
        for ($i = 1; $i <= 5; $i++) {
            $uploaded = handle_image_upload('image_' . $i);
            if ($uploaded) {
                $images[] = $uploaded;
            } elseif (!empty($_POST['existing_image_' . $i])) {
                $images[] = trim($_POST['existing_image_' . $i]);
            }
        }
        $imagesJson = !empty($images) ? json_encode($images) : null;
        $stmt = $pdo->prepare('UPDATE projets SET titre = ?, description = ?, service_id = ?, statut = ?, images = ? WHERE id = ?');
        $stmt->execute([
            trim($_POST['titre']),
            trim($_POST['description']),
            (int) $_POST['service_id'],
            trim($_POST['statut']),
            $imagesJson,
            (int) $_POST['update_id'],
        ]);
        $message = 'Projet mis à jour.';
        log_activity('Mise à jour projet', 'ID ' . (int) $_POST['update_id']);
    } else {
        $images = [];
        for ($i = 1; $i <= 5; $i++) {
            $uploaded = handle_image_upload('image_' . $i);
            if ($uploaded) {
                $images[] = $uploaded;
            }
        }
        $imagesJson = !empty($images) ? json_encode($images) : null;
        $stmt = $pdo->prepare('INSERT INTO projets (titre, description, service_id, statut, images) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            trim($_POST['titre']),
            trim($_POST['description']),
            (int) $_POST['service_id'],
            trim($_POST['statut']),
            $imagesJson,
        ]);
        $message = 'Projet ajouté.';
        log_activity('Ajout projet', trim($_POST['titre']));
    }
}

$projects = $pdo->query('SELECT p.*, s.nom_service FROM projets p LEFT JOIN services s ON s.id = p.service_id ORDER BY p.date_creation DESC')->fetchAll();
$services = $pdo->query('SELECT * FROM services')->fetchAll();
?>
<h2 class="fw-bold mb-4">Gestion des projets</h2>
<?php if ($message): ?>
    <div class="alert alert-info"><?= e($message); ?></div>
<?php endif; ?>
<div class="card p-4 mb-4">
    <h5 class="fw-bold"><?= $editProject ? 'Modifier le projet' : 'Ajouter un projet'; ?></h5>
    <form method="post" class="row g-3" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
        <?php if ($editProject): ?>
            <input type="hidden" name="update_id" value="<?= e($editProject['id']); ?>">
        <?php endif; ?>
        <div class="col-md-6">
            <label class="form-label">Titre</label>
            <input type="text" name="titre" class="form-control" value="<?= e($editProject['titre'] ?? ''); ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Service</label>
            <select name="service_id" class="form-select" required>
                <?php foreach ($services as $service): ?>
                    <option value="<?= e($service['id']); ?>" <?= ($editProject && (int) $editProject['service_id'] === (int) $service['id']) ? 'selected' : '' ?>><?= e($service['nom_service']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Statut</label>
            <select name="statut" class="form-select">
                <option value="en_cours" <?= ($editProject && $editProject['statut'] === 'en_cours') ? 'selected' : '' ?>>En cours</option>
                <option value="termine" <?= ($editProject && $editProject['statut'] === 'termine') ? 'selected' : '' ?>>Terminé</option>
                <option value="planifie" <?= ($editProject && $editProject['statut'] === 'planifie') ? 'selected' : '' ?>>Planifié</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" required><?= e($editProject['description'] ?? ''); ?></textarea>
        </div>
        <?php
        $existingImages = [];
        if ($editProject && !empty($editProject['images'])) {
            $decoded = json_decode($editProject['images'], true);
            if (is_array($decoded)) {
                $existingImages = $decoded;
            }
        }
        for ($i = 1; $i <= 5; $i++):
            $existingImg = $existingImages[$i - 1] ?? '';
        ?>
            <div class="col-md-6">
                <label class="form-label">Image <?= $i; ?> (upload)</label>
                <input type="file" name="image_<?= $i; ?>" class="form-control" accept="image/*">
                <?php if ($existingImg): ?>
                    <input type="hidden" name="existing_image_<?= $i; ?>" value="<?= e($existingImg); ?>">
                    <small class="text-muted">Actuelle: <?= e(basename($existingImg)); ?></small>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
        <div class="col-12">
            <button type="submit" class="btn btn-primary"><?= $editProject ? 'Mettre à jour' : 'Ajouter'; ?></button>
            <?php if ($editProject): ?>
                <a href="<?= SITE_URL ?>/admin/gestion_projets.php" class="btn btn-outline-secondary ms-2">Annuler</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card p-4">
    <h5 class="fw-bold">Liste des projets</h5>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Titre</th>
                <th>Service</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($projects as $project): ?>
                <tr>
                    <td><?= e($project['titre']); ?></td>
                    <td><?= e($project['nom_service'] ?? ''); ?></td>
                    <td><?= e($project['statut']); ?></td>
                    <td>
                        <a class="btn btn-sm btn-outline-primary" href="<?= SITE_URL ?>/admin/gestion_projets.php?edit_id=<?= e($project['id']); ?>">Modifier</a>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                            <input type="hidden" name="delete_id" value="<?= e($project['id']); ?>">
                            <button class="btn btn-sm btn-danger" type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
