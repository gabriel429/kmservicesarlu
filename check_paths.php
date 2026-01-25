<?php
header('Content-Type: text/plain');
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "dirname(SCRIPT_NAME): " . dirname($_SERVER['SCRIPT_NAME']) . "\n";
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
echo "scriptDir: " . $scriptDir . "\n";
$projectRoot = str_replace('/public', '', $scriptDir);
echo "projectRoot: " . $projectRoot . "\n";
$baseUrl = rtrim($protocol . "://" . $host . $projectRoot, '/\\');
echo "baseUrl: " . $baseUrl . "\n";
$assetPath = $protocol . "://" . $host . rtrim($scriptDir, '/\\') . '/';
echo "assetPath: " . $assetPath . "\n";
echo "CSS link would be: " . $assetPath . "assets/css/style.css\n";
?>