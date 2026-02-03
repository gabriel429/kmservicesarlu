<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['product_id']) || empty($data['nom']) || empty($data['telephone'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    exit;
}

$clientInfo = [
    'nom' => trim($data['nom']),
    'telephone' => trim($data['telephone']),
    'quantite' => (int) ($data['quantite'] ?? 1),
    'message' => trim($data['message'] ?? ''),
];

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare('INSERT INTO commandes (produit_id, client_info) VALUES (?, ?)');
    $stmt->execute([(int) $data['product_id'], json_encode($clientInfo, JSON_UNESCAPED_UNICODE)]);

    $whatsappMessage = "Bonjour, je souhaite commander le produit {$data['product_name']}.\n";
    $whatsappMessage .= "Nom: {$clientInfo['nom']}\nTéléphone: {$clientInfo['telephone']}\n";
    $whatsappMessage .= "Quantité: {$clientInfo['quantite']}\n";
    if (!empty($clientInfo['message'])) {
        $whatsappMessage .= "Message: {$clientInfo['message']}";
    }

    echo json_encode([
        'success' => true,
        'whatsapp_number' => WHATSAPP_NUMBER,
        'whatsapp_message' => $whatsappMessage
    ]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}
