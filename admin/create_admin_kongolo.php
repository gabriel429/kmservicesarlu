<?php
require_once __DIR__ . '/../includes/functions.php';

$nom = 'kongolo';
$email = 'kongolo@kmservices.local';
$mot_de_passe = 'DG2026@01';
$role = 'admin';

try {
    $pdo = getPDO();

    $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    $passwordHash = password_hash($mot_de_passe, PASSWORD_BCRYPT);

    if ($existing) {
        $update = $pdo->prepare('UPDATE utilisateurs SET nom = :nom, password_hash = :password_hash, role = :role WHERE id = :id');
        $update->execute([
            'nom' => $nom,
            'password_hash' => $passwordHash,
            'role' => $role,
            'id' => $existing['id']
        ]);
        $message = 'Compte admin mis à jour.';
    } else {
        $insert = $pdo->prepare('INSERT INTO utilisateurs (nom, email, password_hash, role) VALUES (:nom, :email, :password_hash, :role)');
        $insert->execute([
            'nom' => $nom,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role
        ]);
        $message = 'Compte admin créé.';
    }
} catch (Throwable $e) {
    $message = 'Erreur: ' . $e->getMessage();
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Création admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="alert alert-info">
            <strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>
        <p class="mb-1">Identifiant: <strong><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></strong></p>
        <p class="mb-3">Mot de passe: <strong><?= htmlspecialchars($mot_de_passe, ENT_QUOTES, 'UTF-8'); ?></strong></p>
        <p class="text-muted">Supprimez ce fichier après usage pour la sécurité.</p>
        <a class="btn btn-primary" href="<?= SITE_URL ?>/admin/login.php">Aller au login</a>
    </div>
</body>
</html>
