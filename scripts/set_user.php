#!/usr/bin/env php
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/MySQL.php';

$username = $argv[1] ?? null;
$password = $argv[2] ?? null;
$email    = $argv[3] ?? ($username ? ($username . '@kmservicesarlu.cd') : null);
$role     = $argv[4] ?? 'admin';

if (!$username || !$password) {
    fwrite(STDERR, "Usage: php scripts/set_user.php <username> <password> [email] [role]\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_BCRYPT);

try {
    $existing = MySQLCore::fetch(
        "SELECT id FROM users WHERE username = ? OR email = ?",
        [$username, $email]
    );

    if ($existing && isset($existing['id'])) {
        MySQLCore::execute(
            "UPDATE users SET password = ?, role = ?, active = 1 WHERE id = ?",
            [$hash, $role, (int)$existing['id']]
        );
        echo "✅ Mot de passe mis à jour pour '$username' (role: $role)\n";
    } else {
        MySQLCore::execute(
            "INSERT INTO users (username, email, password, role, active) VALUES (?, ?, ?, ?, 1)",
            [$username, $email, $hash, $role]
        );
        echo "✅ Utilisateur créé: '$username' (role: $role)\n";
    }
    exit(0);
} catch (Exception $e) {
    fwrite(STDERR, "❌ Erreur: " . $e->getMessage() . "\n");
    exit(1);
}
