<?php
// Vérifier l'état de la table messages_contact

require_once __DIR__ . '/includes/functions.php';

echo '<pre style="background:#f4f4f4; padding:20px; font-family:monospace;">';
echo "=== VÉRIFICATION TABLE messages_contact ===\n\n";

try {
    $pdo = getPDO();
    echo "✓ Connexion BD réussie\n\n";
    
    // 1. Vérifier si la table existe
    echo "1. VÉRIFICATION TABLE:\n";
    try {
        $result = $pdo->query("SELECT 1 FROM messages_contact LIMIT 1");
        echo "✓ Table messages_contact existe\n\n";
    } catch (PDOException $e) {
        echo "✗ Table messages_contact n'existe pas!\n";
        echo "Erreur: " . $e->getMessage() . "\n\n";
        die();
    }
    
    // 2. Vérifier la structure de la table
    echo "2. STRUCTURE DE LA TABLE:\n";
    $columns = $pdo->query("DESCRIBE messages_contact")->fetchAll();
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")" . ($col['Null'] == 'NO' ? ' NOT NULL' : ' NULL') . "\n";
    }
    echo "\n";
    
    // 3. Compter les messages
    echo "3. MESSAGES EN BD:\n";
    $count = $pdo->query("SELECT COUNT(*) FROM messages_contact")->fetchColumn();
    echo "Total de messages: " . $count . "\n\n";
    
    // 4. Afficher les 5 derniers messages
    echo "4. DERNIERS MESSAGES:\n";
    $messages = $pdo->query("SELECT * FROM messages_contact ORDER BY date_message DESC LIMIT 5")->fetchAll();
    
    if (empty($messages)) {
        echo "Aucun message\n\n";
    } else {
        foreach ($messages as $msg) {
            echo "  ID: " . $msg['id'] . " | Nom: " . $msg['nom'] . " | Email: " . $msg['email'] . "\n";
            echo "    Message: " . substr($msg['message'], 0, 60) . "...\n";
            echo "    Date: " . $msg['date_message'] . "\n";
            if (!empty($msg['telephone'])) {
                echo "    Téléphone: " . $msg['telephone'] . "\n";
            }
            echo "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}

echo "=== FIN VÉRIFICATION ===\n";
echo '</pre>';
?>
