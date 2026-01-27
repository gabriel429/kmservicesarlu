<?php
/**
 * Endpoint de diagnostic - Vérifie et crée les tables
 * Accédez à: /diagnostic.php
 */

header('Content-Type: application/json; charset=utf-8');

@mkdir(__DIR__ . '/logs', 0755, true);
$logFile = __DIR__ . '/logs/init.log';

$response = [
    'timestamp' => date('Y-m-d H:i:s'),
    'status' => 'checking'
];

try {
    require_once __DIR__ . '/config/config.php';
    
    // Forcer la création des tables
    require_once __DIR__ . '/app/init_tables.php';
    
    // Vérifier la connexion
    require_once __DIR__ . '/app/MySQL.php';
    $result = MySQLCore::fetch("SELECT 1");
    
    if ($result) {
        $response['db_connection'] = 'OK';
    }
    
    // Vérifier la table quote_requests
    try {
        $quoteCount = MySQLCore::fetch("SELECT COUNT(*) as count FROM quote_requests");
        $response['quote_requests_table'] = 'EXISTS';
        $response['quote_requests_count'] = $quoteCount['count'];
    } catch (Throwable $e) {
        $response['quote_requests_table'] = 'NOT_FOUND';
        $response['quote_requests_error'] = substr($e->getMessage(), 0, 100);
    }
    
    $response['status'] = 'success';
    $response['log_file'] = file_exists($logFile) ? file_get_contents($logFile) : 'no logs';
    
} catch (Throwable $e) {
    $response['status'] = 'error';
    $response['error'] = $e->getMessage();
    $response['log_file'] = file_exists($logFile) ? file_get_contents($logFile) : 'no logs';
}

http_response_code($response['status'] === 'success' ? 200 : 500);
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
