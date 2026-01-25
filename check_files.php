<?php
echo "<h1>Diagnostic</h1>";
echo "File: app/Database.php<br>";
$content = file_get_contents('app/Database.php');
echo "First 20 chars (hex): " . bin2hex(substr($content, 0, 20)) . "<br>";
echo "First line: " . htmlspecialchars(strtok($content, "\n")) . "<br>";
?>
