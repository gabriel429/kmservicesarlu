<?php
// TEST FILE - Debug contact form
$page_title = 'Contact Test - KM SERVICES';

echo '<pre style="background:#f4f4f4; padding:20px; font-family:monospace;">';
echo "=== CONTACT FORM DEBUG ===\n\n";

// Test 1: Check includes
echo "Test 1: Loading includes...\n";
try {
    include __DIR__ . '/includes/header.php';
    echo "✓ Header loaded\n";
} catch (Exception $e) {
    echo "✗ Header error: " . $e->getMessage() . "\n";
    die();
}

echo "\nTest 2: Checking CSRF token function...\n";
if (function_exists('csrf_token')) {
    echo "✓ csrf_token() exists\n";
    $token = csrf_token();
    echo "✓ Token generated: " . substr($token, 0, 20) . "...\n";
} else {
    echo "✗ csrf_token() not found\n";
}

echo "\nTest 3: Checking e() function...\n";
if (function_exists('e')) {
    echo "✓ e() function exists\n";
} else {
    echo "✗ e() function not found\n";
}

echo "\nTest 4: Session status...\n";
echo "Session status: " . session_status() . " (1=active, 2=none)\n";
echo "Session ID: " . session_id() . "\n";

echo "\n=== END DEBUG ===\n";
echo '</pre>';
?>
