<?php
$admin_title = 'Gestion produits';
include __DIR__ . '/../includes/admin_header.php';

$pdo = getPDO();
$message = null;
$editProduct = null;

if (!empty($_GET['edit_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM produits WHERE id = ?');
    $stmt->execute([(int) $_GET['edit_id']]);
    $editProduct = $stmt->fetch();
}

if (is_post()) {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $message = 'Session expirée.';
    } elseif (isset($_POST['delete_id'])) {
        $stmt = $pdo->prepare('DELETE FROM produits WHERE id = ?');
        $stmt->execute([(int) $_POST['delete_id']]);
        $message = 'Produit supprimé.';
        log_activity('Suppression produit', 'ID ' . (int) $_POST['delete_id']);
    } elseif (!empty($_POST['update_id'])) {
        $imagePath = handle_image_upload('image_file');
        $currentImage = trim($_POST['image'] ?? '');
        $finalImage = $imagePath ?: $currentImage;
        $stmt = $pdo->prepare('UPDATE produits SET nom_produit = ?, description = ?, prix = ?, image = ?, reference = ? WHERE id = ?');
        $stmt->execute([
            trim($_POST['nom_produit']),
            trim($_POST['description']),
            (float) $_POST['prix'],
            $finalImage,
            trim($_POST['reference']),
            (int) $_POST['update_id'],
        ]);
        $message = 'Produit mis à jour.';
        log_activity('Mise à jour produit', 'ID ' . (int) $_POST['update_id']);
    } else {
        $imagePath = handle_image_upload('image_file');
        $stmt = $pdo->prepare('INSERT INTO produits (nom_produit, description, prix, image, reference) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            trim($_POST['nom_produit']),
            trim($_POST['description']),
            (float) $_POST['prix'],
            $imagePath ?: trim($_POST['image']),
            trim($_POST['reference']),
        ]);
        $message = 'Produit ajouté.';
        log_activity('Ajout produit', 'Réf ' . trim($_POST['reference']));
    }
}

$products = $pdo->query('SELECT * FROM produits ORDER BY date_creation DESC')->fetchAll();
?>
<h2 class="fw-bold mb-4">Gestion des produits</h2>
<?php if ($message): ?>
    <div class="alert alert-info"><?= e($message); ?></div>
<?php endif; ?>
<div class="card p-4 mb-4">
    <h5 class="fw-bold"><?= $editProduct ? 'Modifier le produit' : 'Ajouter un produit'; ?></h5>
    <form method="post" class="row g-3" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
        <?php if ($editProduct): ?>
            <input type="hidden" name="update_id" value="<?= e($editProduct['id']); ?>">
        <?php endif; ?>
        <div class="col-md-6">
            <label class="form-label">Nom</label>
            <input type="text" name="nom_produit" class="form-control" value="<?= e($editProduct['nom_produit'] ?? ''); ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Référence</label>
            <input type="text" name="reference" class="form-control" value="<?= e($editProduct['reference'] ?? ''); ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Prix</label>
            <input type="number" step="0.01" name="prix" class="form-control" value="<?= e($editProduct['prix'] ?? ''); ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Image (upload)</label>
            <input type="file" name="image_file" class="form-control" accept="image/*">
        </div>
        <div class="col-md-4">
            <label class="form-label">Image (URL)</label>
            <input type="text" name="image" class="form-control" value="<?= e($editProduct['image'] ?? ''); ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" required><?= e($editProduct['description'] ?? ''); ?></textarea>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary"><?= $editProduct ? 'Mettre à jour' : 'Ajouter'; ?></button>
            <?php if ($editProduct): ?>
                <a href="<?= SITE_URL ?>/admin/gestion_produits.php" class="btn btn-outline-secondary ms-2">Annuler</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card p-4">
    <h5 class="fw-bold">Liste des produits</h5>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Nom</th>
                <th>Référence</th>
                <th>Prix</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= e($product['nom_produit']); ?></td>
                    <td><?= e($product['reference']); ?></td>
                    <td><?= format_price((float) $product['prix']); ?></td>
                    <td>
                        <a class="btn btn-sm btn-outline-primary" href="<?= SITE_URL ?>/admin/gestion_produits.php?edit_id=<?= e($product['id']); ?>">Modifier</a>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                            <input type="hidden" name="delete_id" value="<?= e($product['id']); ?>">
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
