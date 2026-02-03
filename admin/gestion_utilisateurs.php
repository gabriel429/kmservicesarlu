<?php
$admin_title = 'Gestion utilisateurs';
include __DIR__ . '/../includes/admin_header.php';

if (($_SESSION['user']['role'] ?? 'editor') === 'editor') {
    redirect(SITE_URL . '/admin/dashboard.php');
}

$pdo = getPDO();
$message = null;
$editUser = null;

if (!empty($_GET['edit_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE id = ?');
    $stmt->execute([(int) $_GET['edit_id']]);
    $editUser = $stmt->fetch();
}

if (is_post()) {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $message = 'Session expirée.';
    } elseif (isset($_POST['delete_id'])) {
        $userId = (int) $_POST['delete_id'];
        if ($userId != $_SESSION['user']['id']) {
            $stmt = $pdo->prepare('DELETE FROM utilisateurs WHERE id = ?');
            $stmt->execute([$userId]);
            $message = 'Utilisateur supprimé.';
            log_activity('Suppression utilisateur', 'ID ' . $userId);
        } else {
            $message = 'Vous ne pouvez pas supprimer votre propre compte.';
        }
    } elseif (!empty($_POST['toggle_status_id'])) {
        $userId = (int) $_POST['toggle_status_id'];
        $stmt = $pdo->prepare('SELECT statut FROM utilisateurs WHERE id = ?');
        $stmt->execute([$userId]);
        $currentStatus = $stmt->fetchColumn();
        $newStatus = ($currentStatus === 'bloque') ? 'actif' : 'bloque';
        $stmt = $pdo->prepare('UPDATE utilisateurs SET statut = ? WHERE id = ?');
        $stmt->execute([$newStatus, $userId]);
        $message = 'Statut mis à jour.';
        log_activity('Changement statut utilisateur', 'ID ' . $userId);
    } elseif (!empty($_POST['update_id'])) {
        $userId = (int) $_POST['update_id'];
        if (!empty($_POST['password'])) {
            $stmt = $pdo->prepare('UPDATE utilisateurs SET nom = ?, email = ?, password_hash = ?, role = ? WHERE id = ?');
            $stmt->execute([
                trim($_POST['nom']),
                trim($_POST['email']),
                password_hash($_POST['password'], PASSWORD_BCRYPT),
                trim($_POST['role']),
                $userId,
            ]);
        } else {
            $stmt = $pdo->prepare('UPDATE utilisateurs SET nom = ?, email = ?, role = ? WHERE id = ?');
            $stmt->execute([
                trim($_POST['nom']),
                trim($_POST['email']),
                trim($_POST['role']),
                $userId,
            ]);
        }
        $message = 'Utilisateur modifié.';
        log_activity('Modification utilisateur', 'ID ' . $userId);
        redirect(SITE_URL . '/admin/gestion_utilisateurs.php');
    } else {
        $stmt = $pdo->prepare('INSERT INTO utilisateurs (nom, email, password_hash, role, statut) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            trim($_POST['nom']),
            trim($_POST['email']),
            password_hash($_POST['password'], PASSWORD_BCRYPT),
            trim($_POST['role']),
            'actif',
        ]);
        $message = 'Utilisateur ajouté.';
        log_activity('Ajout utilisateur', trim($_POST['email']));
    }
}

$users = $pdo->query('SELECT * FROM utilisateurs ORDER BY date_creation DESC')->fetchAll();
?>
<h2 class="fw-bold mb-4">Gestion des utilisateurs</h2>
<?php if ($message): ?>
    <div class="alert alert-info"><?= e($message); ?></div>
<?php endif; ?>
<div class="card p-4 mb-4">
    <h5 class="fw-bold"><?= $editUser ? 'Modifier l\'utilisateur' : 'Ajouter un utilisateur' ?></h5>
    <form method="post" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
        <?php if ($editUser): ?>
            <input type="hidden" name="update_id" value="<?= e($editUser['id']); ?>">
        <?php endif; ?>
        <div class="col-md-4">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" class="form-control" value="<?= e($editUser['nom'] ?? ''); ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= e($editUser['email'] ?? ''); ?>" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Rôle</label>
            <select name="role" class="form-select">
                <option value="admin" <?= ($editUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="editor" <?= ($editUser['role'] ?? '') === 'editor' ? 'selected' : '' ?>>Editor</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Mot de passe<?= $editUser ? ' (laisser vide pour ne pas changer)' : '' ?></label>
            <input type="password" name="password" class="form-control" <?= $editUser ? '' : 'required' ?>>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary"><?= $editUser ? 'Modifier' : 'Ajouter' ?></button>
            <?php if ($editUser): ?>
                <a href="<?= SITE_URL ?>/admin/gestion_utilisateurs.php" class="btn btn-outline-secondary ms-2">Annuler</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card p-4">
    <h5 class="fw-bold">Liste des utilisateurs</h5>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Statut</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= e($user['nom']); ?></td>
                    <td><?= e($user['email']); ?></td>
                    <td><?= e($user['role']); ?></td>
                    <td>
                        <?php 
                        $statut = $user['statut'] ?? 'actif';
                        $badgeClass = $statut === 'actif' ? 'bg-success' : 'bg-danger';
                        ?>
                        <span class="badge <?= $badgeClass ?>"><?= e(ucfirst($statut)); ?></span>
                    </td>
                    <td><?= e($user['date_creation']); ?></td>
                    <td>
                        <?php if ($user['id'] != $_SESSION['user']['id']): ?>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                                <input type="hidden" name="toggle_status_id" value="<?= e($user['id']); ?>">
                                <button type="submit" class="btn btn-sm <?= ($statut === 'actif') ? 'btn-warning' : 'btn-success' ?>">
                                    <?= ($statut === 'actif') ? 'Bloquer' : 'Débloquer' ?>
                                </button>
                            </form>
                            <a href="<?= SITE_URL ?>/admin/gestion_utilisateurs.php?edit_id=<?= e($user['id']); ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                            <form method="post" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?');">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                                <input type="hidden" name="delete_id" value="<?= e($user['id']); ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                            </form>
                        <?php else: ?>
                            <span class="badge bg-secondary">Vous</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
