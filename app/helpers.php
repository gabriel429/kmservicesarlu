<?php
/**
 * Fonctions utilitaires pour KM Services
 */

/**
 * Valider une adresse email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Nettoyer une entrée utilisateur
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Valider un numéro de téléphone
 */
function validatePhone($phone) {
    return preg_match('/^\+?[\d\s\-\(\)]{7,}$/', $phone);
}

/**
 * Générer un slug à partir d'une chaîne
 */
function generateSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

/**
 * Formater une date
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    try {
        $dateObj = new DateTime($date);
        return $dateObj->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Formater un montant en devise
 */
function formatMoney($amount, $currency = '$') {
    return $currency . number_format($amount, 2, '.', ',');
}

/**
 * Vérifier si un utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obtenir l'utilisateur actuellement connecté
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = Database::getInstance();
    return $db->fetch(
        "SELECT id, username, email, role FROM users WHERE id = ?",
        [$_SESSION['user_id']]
    );
}

/**
 * Rediriger après authentification
 */
function requireLogin($redirectTo = null) {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $redirectTo ?? $_SERVER['REQUEST_URI'];
        header('Location: ' . APP_URL . 'admin/login');
        exit;
    }
}

/**
 * Vérifier les permissions d'un rôle
 */
function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

/**
 * Limiter le texte
 */
function limitText($text, $limit = 100, $suffix = '...') {
    if (strlen($text) > $limit) {
        return substr($text, 0, $limit) . $suffix;
    }
    return $text;
}

/**
 * Obtenir le nom du mois
 */
function getMonthName($month) {
    $months = [
        'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
    ];
    return $months[$month - 1] ?? '';
}

/**
 * Calculer le temps écoulé (ex: "il y a 2 heures")
 */
function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return "à l'instant";
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return "il y a $minutes minute" . ($minutes > 1 ? 's' : '');
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return "il y a $hours heure" . ($hours > 1 ? 's' : '');
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return "il y a $days jour" . ($days > 1 ? 's' : '');
    } else {
        return date('d/m/Y', $time);
    }
}

/**
 * Générer un numéro de commande unique
 */
function generateOrderNumber() {
    return 'CMD-' . date('YmdHis') . '-' . rand(1000, 9999);
}

/**
 * Envoyer un email
 */
function sendEmail($to, $subject, $message, $from = 'noreply@kmservices.cd') {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: $from\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Log des erreurs
 */
function logError($message, $level = 'error') {
    $logFile = BASE_PATH . 'logs/error.log';
    
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$level] $message\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Obtenir la classe CSS selon le statut
 */
function getStatusClass($status) {
    $classes = [
        'nouveau' => 'status-new',
        'traite' => 'status-processed',
        'archive' => 'status-archived',
        'confirme' => 'status-confirmed',
        'en_attente' => 'status-pending',
        'contacte' => 'status-contacted',
        'complete' => 'status-completed',
        'refuse' => 'status-refused',
    ];
    
    return $classes[$status] ?? 'status-default';
}

/**
 * Obtenir le label du statut en français
 */
function getStatusLabel($status) {
    $labels = [
        'nouveau' => 'Nouveau',
        'traite' => 'Traité',
        'archive' => 'Archivé',
        'confirme' => 'Confirmée',
        'en_attente' => 'En attente',
        'contacte' => 'Contacté',
        'complete' => 'Complète',
        'refuse' => 'Refusée',
        'realise' => 'Réalisé',
        'termine' => 'Réalisé',
        'en_cours' => 'En cours',
        'en_attente' => 'En attente',
    ];
    
    return $labels[$status] ?? ucfirst($status);
}

?>
