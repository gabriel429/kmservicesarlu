<?php
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    redirect(SITE_URL . '/admin/dashboard.php');
}

$error = null;
if (is_post()) {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $error = 'Session expirée. Veuillez réessayer.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        if (login($email, $password)) {
            log_activity('Connexion admin', $email);
            redirect(SITE_URL . '/admin/dashboard.php');
        }
        $error = 'Identifiants invalides.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card p-4">
                <h4 class="fw-bold mb-3">Connexion admin</h4>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error); ?></div>
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
