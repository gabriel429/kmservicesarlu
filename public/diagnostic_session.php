<?php
/**
 * Diagnostic de l'authentification
 */
session_start();

echo "<h2>Diagnostic Authentification</h2>";
echo "<pre>";
echo "Domain actuel: " . $_SERVER['HTTP_HOST'] . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "ACTIVE" : "INACTIVE") . "\n";
echo "Cookies reçus: " . print_r($_COOKIE, true) . "\n";
echo "SESSION array: " . print_r($_SESSION, true) . "\n";
echo "Admin ID: " . ($_SESSION['admin_id'] ?? 'NOT SET') . "\n";
echo "Admin Role: " . ($_SESSION['admin_role'] ?? 'NOT SET') . "\n";
echo "</pre>";

echo "<h3>Test d'enregistrement de session</h3>";
$_SESSION['test_time'] = time();
echo "Test écrit à: " . $_SESSION['test_time'];
?>
