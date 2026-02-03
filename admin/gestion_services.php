<?php
$admin_title = 'Gestion services';
include __DIR__ . '/../includes/admin_header.php';

if (($_SESSION['user']['role'] ?? 'editor') === 'editor') {
    redirect(SITE_URL . '/admin/dashboard.php');
}

$pdo = getPDO();
$message = null;
$editService = null;
$hasDisplayOrder = true;

try {
    $pdo->query('SELECT display_order FROM services LIMIT 1');
} catch (PDOException $e) {
    $hasDisplayOrder = false;
}

if (!empty($_GET['edit_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM services WHERE id = ?');
    $stmt->execute([(int) $_GET['edit_id']]);
    $editService = $stmt->fetch();
}

if (is_post()) {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $message = 'Session expirée.';
    } elseif (isset($_POST['delete_id'])) {
        $stmt = $pdo->prepare('DELETE FROM services WHERE id = ?');
        $stmt->execute([(int) $_POST['delete_id']]);
        $message = 'Service supprimé.';
        log_activity('Suppression service', 'ID ' . (int) $_POST['delete_id']);
    } elseif (!empty($_POST['update_id'])) {
        $uploadedImage = handle_image_upload('image_file');
        $currentIcon = trim($_POST['icone'] ?? '');
        $finalIcon = $uploadedImage ?: $currentIcon;

        if ($hasDisplayOrder) {
            $stmt = $pdo->prepare('UPDATE services SET nom_service = ?, description = ?, icone = ?, display_order = ? WHERE id = ?');
            $stmt->execute([
                trim($_POST['nom_service']),
                trim($_POST['description']),
                $finalIcon ?: null,
                (int) $_POST['display_order'],
                (int) $_POST['update_id'],
            ]);
        } else {
            $stmt = $pdo->prepare('UPDATE services SET nom_service = ?, description = ?, icone = ? WHERE id = ?');
            $stmt->execute([
                trim($_POST['nom_service']),
                trim($_POST['description']),
                $finalIcon ?: null,
                (int) $_POST['update_id'],
            ]);
        }

        $message = 'Service mis à jour.';
        log_activity('Mise à jour service', 'ID ' . (int) $_POST['update_id']);
    } else {
        $uploadedImage = handle_image_upload('image_file');
        $iconValue = $uploadedImage ?: trim($_POST['icone']);

        if ($hasDisplayOrder) {
            $stmt = $pdo->prepare('INSERT INTO services (nom_service, description, icone, display_order) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                trim($_POST['nom_service']),
                trim($_POST['description']),
                $iconValue ?: null,
                (int) $_POST['display_order'],
            ]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO services (nom_service, description, icone) VALUES (?, ?, ?)');
            $stmt->execute([
                trim($_POST['nom_service']),
                trim($_POST['description']),
                $iconValue ?: null,
            ]);
        }

        $message = 'Service ajouté.';
        log_activity('Ajout service', 'Nom ' . trim($_POST['nom_service']));
    }
}

if ($hasDisplayOrder) {
    $services = $pdo->query('SELECT * FROM services ORDER BY display_order ASC, id ASC')->fetchAll();
} else {
    $services = $pdo->query('SELECT * FROM services ORDER BY id ASC')->fetchAll();
}
?>
<h2 class="fw-bold mb-4">Gestion des services</h2>
<?php if ($message): ?>
    <div class="alert alert-info"><?= e($message); ?></div>
<?php endif; ?>
<div class="card p-4 mb-4">
    <h5 class="fw-bold"><?= $editService ? 'Modifier le service' : 'Ajouter un service'; ?></h5>
    <form method="post" class="row g-3" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
        <?php if ($editService): ?>
            <input type="hidden" name="update_id" value="<?= e($editService['id']); ?>">
        <?php endif; ?>
        <div class="col-md-6">
            <label class="form-label">Nom</label>
            <input type="text" name="nom_service" class="form-control" value="<?= e($editService['nom_service'] ?? ''); ?>" required>
        </div>
        <?php if ($hasDisplayOrder): ?>
            <div class="col-md-3">
                <label class="form-label">Ordre d'affichage</label>
                <input type="number" name="display_order" class="form-control" value="<?= e($editService['display_order'] ?? 1); ?>" min="1" required>
            </div>
        <?php else: ?>
            <input type="hidden" name="display_order" value="1">
        <?php endif; ?>
        <div class="col-md-3">
            <label class="form-label">Image (upload)</label>
            <input type="file" name="image_file" class="form-control" accept="image/*">
        </div>
        <div class="col-md-3">
            <label class="form-label">Icône ou URL image</label>
            <input type="text" name="icone" class="form-control" value="<?= e($editService['icone'] ?? 'fa-solid fa-briefcase'); ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" required><?= e($editService['description'] ?? ''); ?></textarea>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary"><?= $editService ? 'Mettre à jour' : 'Ajouter'; ?></button>
            <?php if ($editService): ?>
                <a href="<?= SITE_URL ?>/admin/gestion_services.php" class="btn btn-outline-secondary ms-2">Annuler</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card p-4">
    <h5 class="fw-bold">Liste des services</h5>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
            <tr>
                <?php if ($hasDisplayOrder): ?>
                    <th>Ordre</th>
                <?php endif; ?>
                <th>Nom</th>
                <th>Icône</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($services as $service): ?>
                <tr>
                    <?php if ($hasDisplayOrder): ?>
                        <td><?= e($service['display_order'] ?? 1); ?></td>
                    <?php endif; ?>
                    <td><?= e($service['nom_service']); ?></td>
                    <td><?= e($service['icone'] ?? ''); ?></td>
                    <td>
                        <a class="btn btn-sm btn-outline-primary" href="<?= SITE_URL ?>/admin/gestion_services.php?edit_id=<?= e($service['id']); ?>">Modifier</a>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                            <input type="hidden" name="delete_id" value="<?= e($service['id']); ?>">
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
