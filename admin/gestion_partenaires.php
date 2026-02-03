<?php
$admin_title = 'Gestion partenaires';
include __DIR__ . '/../includes/admin_header.php';

if (($_SESSION['user']['role'] ?? 'editor') === 'editor') {
    redirect(SITE_URL . '/admin/dashboard.php');
}

$message = null;
$partners = read_partners();
$editIndex = isset($_GET['edit']) ? (int) $_GET['edit'] : null;
$editPartner = ($editIndex !== null && isset($partners[$editIndex])) ? $partners[$editIndex] : null;

if (is_post()) {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $message = 'Session expirée.';
    } elseif (isset($_POST['delete_id'])) {
        $deleteId = (int) $_POST['delete_id'];
        if (isset($partners[$deleteId])) {
            unset($partners[$deleteId]);
            $partners = array_values($partners);
            save_partners($partners);
            $message = 'Partenaire supprimé.';
            log_activity('Suppression partenaire', 'Index ' . $deleteId);
        }
    } else {
        $name = trim($_POST['name'] ?? '');
        $url = trim($_POST['url'] ?? '#');
        $logoUpload = handle_image_upload('logo_file');
        $logo = $logoUpload ?: trim($_POST['logo'] ?? '');

        if ($name === '' || $logo === '') {
            $message = 'Nom et logo requis.';
        } else {
            $payload = [
                'name' => $name,
                'logo' => $logo,
                'url' => $url === '' ? '#' : $url,
            ];

            if (!empty($_POST['update_id'])) {
                $updateId = (int) $_POST['update_id'];
                if (isset($partners[$updateId])) {
                    if (empty($logo) && !empty($partners[$updateId]['logo'])) {
                        $payload['logo'] = $partners[$updateId]['logo'];
                    }
                    $partners[$updateId] = $payload;
                    save_partners($partners);
                    $message = 'Partenaire mis à jour.';
                    log_activity('Mise à jour partenaire', 'Index ' . $updateId);
                }
            } else {
                $partners[] = $payload;
                save_partners($partners);
                $message = 'Partenaire ajouté.';
                log_activity('Ajout partenaire', $name);
            }
        }
    }
}
?>
<h2 class="fw-bold mb-4">Gestion des partenaires</h2>
<?php if ($message): ?>
    <div class="alert alert-info"><?= e($message); ?></div>
<?php endif; ?>

<div class="card p-4 mb-4">
    <h5 class="fw-bold"><?= $editPartner ? 'Modifier le partenaire' : 'Ajouter un partenaire'; ?></h5>
    <form method="post" class="row g-3" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
        <?php if ($editPartner): ?>
            <input type="hidden" name="update_id" value="<?= e((string) $editIndex); ?>">
        <?php endif; ?>
        <div class="col-md-6">
            <label class="form-label">Nom</label>
            <input type="text" name="name" class="form-control" value="<?= e($editPartner['name'] ?? ''); ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">URL (optionnel)</label>
            <input type="text" name="url" class="form-control" value="<?= e($editPartner['url'] ?? '#'); ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Logo (upload)</label>
            <input type="file" name="logo_file" class="form-control" accept="image/*">
            <?php if (!empty($editPartner['logo'])): ?>
                <small class="text-muted">Actuel: <?= e(basename($editPartner['logo'])); ?></small>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label">Logo (URL / chemin)</label>
            <input type="text" name="logo" class="form-control" value="<?= e($editPartner['logo'] ?? ''); ?>">
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary"><?= $editPartner ? 'Mettre à jour' : 'Ajouter'; ?></button>
            <?php if ($editPartner): ?>
                <a href="<?= SITE_URL ?>/admin/gestion_partenaires.php" class="btn btn-outline-secondary ms-2">Annuler</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card p-4">
    <h5 class="fw-bold">Liste des partenaires</h5>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
            <tr>
                <th>Logo</th>
                <th>Nom</th>
                <th>URL</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($partners as $index => $partner): ?>
                <tr>
                    <td>
                        <?php if (!empty($partner['logo'])): ?>
                            <img src="<?= SITE_URL ?>/<?= e($partner['logo']); ?>" alt="<?= e($partner['name'] ?? 'Partenaire'); ?>" style="height:32px;">
                        <?php endif; ?>
                    </td>
                    <td><?= e($partner['name'] ?? ''); ?></td>
                    <td><?= e($partner['url'] ?? '#'); ?></td>
                    <td>
                        <a class="btn btn-sm btn-outline-primary" href="<?= SITE_URL ?>/admin/gestion_partenaires.php?edit=<?= e((string) $index); ?>">Modifier</a>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                            <input type="hidden" name="delete_id" value="<?= e((string) $index); ?>">
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
