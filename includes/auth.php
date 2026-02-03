<?php
require_once __DIR__ . '/functions.php';

function ensure_default_admin(): void
{
    $pdo = getPDO();
    $count = (int) $pdo->query('SELECT COUNT(*) FROM utilisateurs')->fetchColumn();

    if ($count === 0) {
        $password = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO utilisateurs (nom, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute(['Administrateur', 'admin@kmservices.local', $password, 'admin']);
    }
}

function login(string $email, string $password): bool
{
    $pdo = getPDO();
    ensure_default_admin();

    $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $statut = $user['statut'] ?? 'actif';
        if ($statut === 'bloque') {
            return false;
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user'] = [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        return true;
    }

    return false;
}

function logout(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];
    session_destroy();
}

function is_logged_in(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($_SESSION['user']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect(SITE_URL . '/admin/login.php');
    }
}
