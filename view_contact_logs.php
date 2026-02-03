<?php
// Afficher les logs de contact

$logFile = __DIR__ . '/logs_contact.txt';

echo '<pre style="background:#f4f4f4; padding:20px; font-family:monospace; white-space: pre-wrap; word-wrap: break-word;">';
echo "=== LOGS CONTACT FORM ===\n\n";

if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    if (empty($content)) {
        echo "Aucun log pour le moment.\n";
    } else {
        echo $content;
    }
} else {
    echo "Fichier de log n'existe pas encore.\n";
}

echo "\n=== FIN LOGS ===\n";
echo "\n<a href='logs_contact.txt'>Télécharger les logs bruts</a>\n";
echo '</pre>';
?>
