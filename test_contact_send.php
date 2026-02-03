<?php
// TEST SCRIPT - Envoyer un message de test

require_once __DIR__ . '/includes/functions.php';

echo '<pre style="background:#f4f4f4; padding:20px; font-family:monospace;">';
echo "=== TEST ENVOI MESSAGE CONTACT ===\n\n";

// Données de test
$testData = [
    'nom' => 'Test Admin',
    'email' => 'test@example.com',
    'telephone' => '+243 892 017 793',
    'message' => 'Ceci est un message de test envoyé automatiquement pour tester le formulaire de contact.'
];

echo "Données du test:\n";
echo "  Nom: " . $testData['nom'] . "\n";
echo "  Email: " . $testData['email'] . "\n";
echo "  Téléphone: " . $testData['telephone'] . "\n";
echo "  Message: " . $testData['message'] . "\n\n";

// Connecter à la BD
try {
    $pdo = getPDO();
    echo "✓ Connexion BD réussie\n";
    
    // Tester l'insertion
    echo "\nTest d'insertion...\n";
    
    $stmt = $pdo->prepare('INSERT INTO messages_contact (nom, email, message) VALUES (?, ?, ?)');
    $result = $stmt->execute([$testData['nom'], $testData['email'], $testData['message']]);
    
    if ($result) {
        echo "✓ Message inséré avec succès\n";
        $lastId = $pdo->lastInsertId();
        echo "  ID du message: " . $lastId . "\n";
        
        // Tenter de mettre à jour le téléphone si la colonne existe
        try {
            $updateStmt = $pdo->prepare('UPDATE messages_contact SET telephone = ? WHERE id = ?');
            $updateStmt->execute([$testData['telephone'], $lastId]);
            echo "✓ Téléphone mis à jour avec succès\n";
        } catch (PDOException $e) {
            echo "⚠ Colonne téléphone n'existe pas (ce n'est pas grave): " . $e->getMessage() . "\n";
        }
        
        // Vérifier que le message a bien été inséré
        echo "\nVérification du message inséré:\n";
        $checkStmt = $pdo->prepare('SELECT * FROM messages_contact WHERE id = ?');
        $checkStmt->execute([$lastId]);
        $msg = $checkStmt->fetch();
        
        if ($msg) {
            echo "✓ Message trouvé en BD:\n";
            echo "  ID: " . $msg['id'] . "\n";
            echo "  Nom: " . $msg['nom'] . "\n";
            echo "  Email: " . $msg['email'] . "\n";
            if (!empty($msg['telephone'])) {
                echo "  Téléphone: " . $msg['telephone'] . "\n";
            }
            echo "  Message: " . substr($msg['message'], 0, 50) . "...\n";
            echo "  Date: " . $msg['date_message'] . "\n";
        } else {
            echo "✗ Message non trouvé après insertion!\n";
        }
    } else {
        echo "✗ Erreur lors de l'insertion\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== FIN TEST ===\n";
echo "Accède à: https://kmservicesarlu.cd/admin/gestion_messages.php pour voir le message\n";
echo '</pre>';
?>
