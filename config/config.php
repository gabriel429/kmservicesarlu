<?php
/**
 * Configuration KM Services
 */

// Base de données (configurable via variables d'environnement)
$env = function($key, $default = null) {
    $val = getenv($key);
    return ($val === false || $val === '') ? $default : $val;
};

define('DB_DRIVER', $env('DB_DRIVER', 'mysql')); // mysql | pgsql
define('DB_HOST', $env('DB_HOST', 'localhost'));
define('DB_PORT', (int)$env('DB_PORT', DB_DRIVER === 'pgsql' ? 5432 : 3306));
define('DB_USER', $env('DB_USER', 'root'));
define('DB_PASS', $env('DB_PASS', ''));
define('DB_NAME', $env('DB_NAME', 'km_services'));

// Application
define('APP_NAME', 'KM Services');
// Sur Vercel, VERCEL_URL est fourni (sans protocole). Fallback: localhost.
$vercelUrl = $env('VERCEL_URL');
$deployUrl = $env('APP_URL');
define('APP_URL', rtrim(($deployUrl ?: ($vercelUrl ? ('https://' . $vercelUrl . '/') : 'http://localhost:8000/')), '/') . '/');
define('APP_ENV', $env('APP_ENV', $vercelUrl ? 'production' : 'development'));

// Chemins
define('BASE_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('APP_PATH', BASE_PATH . 'app' . DIRECTORY_SEPARATOR);
define('VIEWS_PATH', BASE_PATH . 'views' . DIRECTORY_SEPARATOR);
define('UPLOADS_PATH', BASE_PATH . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR);

// Paramètres de sécurité
define('SESSION_LIFETIME', 3600);
define('MAX_UPLOAD_SIZE', 5242880); // 5MB en bytes

// Pagination
define('ITEMS_PER_PAGE', 12);

// Supabase
define('SUPABASE_URL', $env('SUPABASE_URL', ''));
define('SUPABASE_ANON_KEY', $env('SUPABASE_ANON_KEY', ''));
define('SUPABASE_SERVICE_ROLE_KEY', $env('SUPABASE_SERVICE_ROLE_KEY', ''));
define('SUPABASE_BUCKET', $env('SUPABASE_BUCKET', 'uploads'));

return [
    'database' => [
        'driver' => DB_DRIVER,
        'host' => DB_HOST,
        'port' => DB_PORT,
        'user' => DB_USER,
        'pass' => DB_PASS,
        'name' => DB_NAME,
    ],
    'app' => [
        'name' => APP_NAME,
        'url' => APP_URL,
        'debug' => APP_ENV === 'development',
    ],
    'supabase' => [
        'url' => SUPABASE_URL,
        'anon_key' => SUPABASE_ANON_KEY,
        'service_key' => SUPABASE_SERVICE_ROLE_KEY,
        'bucket' => SUPABASE_BUCKET,
    ],
];
?>
