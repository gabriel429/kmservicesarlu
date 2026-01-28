<?php
/**
 * Handler pour la gestion des commandes
 */
session_start();
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/app/MySQL.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? 'list';

try {
    // Assurer que la table orders existe
    try {
        MySQLCore::execute(
            "CREATE TABLE IF NOT EXISTS orders (
                id INT PRIMARY KEY AUTO_INCREMENT,
                numero_commande VARCHAR(50) UNIQUE NOT NULL,
                nom_client VARCHAR(150) NOT NULL,
                email_client VARCHAR(150) NOT NULL,
                telephone_client VARCHAR(20),
                adresse_livraison TEXT,
                ville VARCHAR(100),
                code_postal VARCHAR(20),
                montant_total DECIMAL(12, 2) NOT NULL,
                statut ENUM('nouvelle', 'confirmee', 'preparee', 'livree', 'annulee') DEFAULT 'nouvelle',
                methode_paiement VARCHAR(50),
                reference_paiement VARCHAR(100),
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                treated_by INT,
                FOREIGN KEY (treated_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_statut (statut),
                INDEX idx_created_at (created_at)
            )"
        );
    } catch (Throwable $te) {
        // Table existe déjà
    }
    
    // Assurer que la table order_items existe
    try {
        MySQLCore::execute(
            "CREATE TABLE IF NOT EXISTS order_items (
                id INT PRIMARY KEY AUTO_INCREMENT,
                order_id INT NOT NULL,
                product_id INT,
                nom_produit VARCHAR(200) NOT NULL,
                prix_unitaire DECIMAL(10, 2) NOT NULL,
                quantite INT NOT NULL,
                montant DECIMAL(12, 2) NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
                INDEX idx_order (order_id)
            )"
        );
    } catch (Throwable $te) {
        // Table existe déjà
    }

    if ($action === 'create') {
        // Créer une nouvelle commande
        $nom_client = trim($_POST['nom_client'] ?? '');
        $email_client = trim($_POST['email_client'] ?? '');
        $telephone_client = trim($_POST['telephone_client'] ?? '');
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantite = isset($_POST['quantite']) ? max(1, (int)$_POST['quantite']) : 1;
        $adresse_livraison = trim($_POST['adresse_livraison'] ?? '');
        $ville = trim($_POST['ville'] ?? '');
        $code_postal = trim($_POST['code_postal'] ?? '');

        if (!$nom_client || !$email_client || !$product_id) {
            throw new Exception('Données requises manquantes');
        }

        // Récupérer les infos du produit
        $product = MySQLCore::fetch(
            "SELECT id, nom, prix FROM products WHERE id = ? AND actif = 1",
            [$product_id]
        );

        if (!$product) {
            throw new Exception('Produit non trouvé');
        }

        // Générer le numéro de commande
        $numero_commande = 'CMD-' . date('YmdHis') . '-' . rand(1000, 9999);

        // Calculer le montant total
        $montant_total = (float)$product['prix'] * $quantite;

        // Insérer la commande
        MySQLCore::execute(
            "INSERT INTO orders (numero_commande, nom_client, email_client, telephone_client, adresse_livraison, ville, code_postal, montant_total, statut) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'nouvelle')",
            [$numero_commande, $nom_client, $email_client, $telephone_client, $adresse_livraison, $ville, $code_postal, $montant_total]
        );

        // Récupérer l'ID de la commande
        $order_id = MySQLCore::fetch(
            "SELECT id FROM orders WHERE numero_commande = ?",
            [$numero_commande]
        )['id'];

        // Insérer les articles de la commande
        MySQLCore::execute(
            "INSERT INTO order_items (order_id, product_id, nom_produit, prix_unitaire, quantite, montant) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [$order_id, $product_id, $product['nom'], $product['prix'], $quantite, $montant_total]
        );

        // Envoyer un email de confirmation
        $subject = 'Confirmation de commande - ' . $numero_commande;
        $body = "Bonjour $nom_client,\n\n";
        $body .= "Votre commande a été reçue avec succès.\n\n";
        $body .= "Numéro de commande: $numero_commande\n";
        $body .= "Produit: " . $product['nom'] . "\n";
        $body .= "Quantité: $quantite\n";
        $body .= "Montant total: \$" . number_format($montant_total, 2) . "\n\n";
        $body .= "Nous vous contacterons bientôt pour confirmer les détails de la livraison.\n\n";
        $body .= "Cordialement,\n";
        $body .= "L'équipe KM Services\n";
        $body .= "contact@kmservicesarlu.cd\n";

        $headers = "From: contact@kmservicesarlu.cd\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "Reply-To: contact@kmservicesarlu.cd\r\n";

        @mail($email_client, $subject, $body, $headers);

        echo json_encode([
            'success' => true,
            'message' => 'Commande créée avec succès',
            'order_id' => $order_id,
            'numero_commande' => $numero_commande
        ]);

    } elseif ($action === 'get') {
        // Récupérer les détails d'une commande
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $order = MySQLCore::fetch(
            "SELECT * FROM orders WHERE id = ?",
            [$id]
        );

        if (!$order) {
            throw new Exception('Commande non trouvée');
        }

        // Récupérer les articles de la commande
        $items = MySQLCore::fetchAll(
            "SELECT * FROM order_items WHERE order_id = ?",
            [$id]
        );

        echo json_encode([
            'success' => true,
            'data' => $order,
            'items' => $items
        ]);

    } elseif ($action === 'update_status') {
        // Mettre à jour le statut
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $statut = trim($_POST['statut'] ?? '');

        if (!in_array($statut, ['nouvelle', 'confirmee', 'preparee', 'livree', 'annulee'])) {
            throw new Exception('Statut invalide');
        }

        MySQLCore::execute(
            "UPDATE orders SET statut = ?, treated_by = ? WHERE id = ?",
            [$statut, $_SESSION['admin_id'] ?? null, $id]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Statut mis à jour'
        ]);

    } elseif ($action === 'delete') {
        // Supprimer une commande
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        MySQLCore::execute(
            "DELETE FROM orders WHERE id = ?",
            [$id]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Commande supprimée'
        ]);

    } else {
        throw new Exception('Action inconnue');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
