<?php
// DEBUG FILE - À SUPPRIMER EN PRODUCTION
// This file helps diagnose database connection and table structure issues

require_once __DIR__ . '/includes/config.php';

echo '<pre style="background:#f4f4f4; padding:20px; font-family:monospace;">';
echo "=== KM SERVICES DEBUG REPORT ===\n\n";

// 1. Check PHP version
echo "PHP Version: " . phpversion() . "\n";

// 2. Check database connection
echo "\n--- DATABASE CONNECTION ---\n";
try {
    $pdo = getPDO();
    echo "✓ Database connection successful\n";
    echo "Driver: " . $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) . "\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// 3. Check tables exist
echo "\n--- TABLE STRUCTURE ---\n";
$tables = ['services', 'projets', 'produits', 'utilisateurs'];
foreach ($tables as $table) {
    try {
        $result = $pdo->query("SELECT COUNT(*) as count FROM $table")->fetch();
        echo "✓ Table '$table': " . $result['count'] . " records\n";
    } catch (Exception $e) {
        echo "✗ Table '$table': ERROR - " . $e->getMessage() . "\n";
    }
}

// 4. Check services table columns
echo "\n--- SERVICES TABLE COLUMNS ---\n";
try {
    $stmt = $pdo->query("DESCRIBE services");
    $columns = $stmt->fetchAll();
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")" . ($col['Null'] == 'NO' ? ' NOT NULL' : '') . "\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// 5. Test services query
echo "\n--- TEST QUERIES ---\n";
try {
    $result = $pdo->query('SELECT * FROM services ORDER BY display_order ASC, id ASC')->fetchAll();
    echo "✓ Services query (with display_order): " . count($result) . " results\n";
} catch (Exception $e) {
    echo "✗ Services query failed: " . $e->getMessage() . "\n";
}

try {
    $result = $pdo->query('SELECT * FROM projets ORDER BY date_creation DESC')->fetchAll();
    echo "✓ Projects query: " . count($result) . " results\n";
} catch (Exception $e) {
    echo "✗ Projects query failed: " . $e->getMessage() . "\n";
}

echo "\n=== END DEBUG REPORT ===\n";
echo '</pre>';
?>
