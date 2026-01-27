<?php
// Traitement du formulaire de devis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once dirname(__DIR__) . '/config/config.php';
        require_once dirname(__DIR__) . '/app/MySQL.php';
        
        // Assurer que la table existe
        try {
            MySQLCore::execute(
                "CREATE TABLE IF NOT EXISTS quote_requests (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    numero_devis VARCHAR(50) UNIQUE NOT NULL,
                    nom VARCHAR(150) NOT NULL,
                    email VARCHAR(150) NOT NULL,
                    telephone VARCHAR(20) NOT NULL,
                    localisation VARCHAR(255),
                    service VARCHAR(100),
                    type_service VARCHAR(100),
                    description LONGTEXT,
                    delai_souhaite VARCHAR(100),
                    budget_estime DECIMAL(12, 2),
                    document_joint VARCHAR(255),
                    statut ENUM('nouveau', 'en_attente', 'contacte', 'accepte', 'refuse') DEFAULT 'nouveau',
                    lu TINYINT DEFAULT 0,
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
            // La table existe déjà
        }
        
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $localisation = trim($_POST['localisation'] ?? '');
        $service = trim($_POST['service'] ?? 'general');
        $type_service = trim($_POST['type_forage'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $delai_souhaite = trim($_POST['delai_souhaite'] ?? '');
        $budget_estime = isset($_POST['budget_estime']) && !empty($_POST['budget_estime']) ? (float)$_POST['budget_estime'] : null;
        
        if ($nom && $email && $telephone && $localisation) {
            // Générer le numéro de devis
            $numero_devis = 'DEV-' . date('YmdHis') . '-' . rand(1000, 9999);
            
            // Traiter le fichier joint s'il existe
            $document_joint = null;
            if (isset($_FILES['document_joint']) && $_FILES['document_joint']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['document_joint'];
                $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
                
                if (in_array($file['type'], $allowed_types) && $file['size'] <= 5242880) { // 5 Mo
                    $filename = 'quote_' . time() . '_' . basename($file['name']);
                    $upload_dir = dirname(__DIR__, 2) . '/public/uploads/devis/';
                    
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                        $document_joint = 'devis/' . $filename;
                    }
                }
            }
            
            // Enregistrer la demande en base de données
            $result = MySQLCore::execute(
                "INSERT INTO quote_requests (numero_devis, nom, email, telephone, localisation, service, type_service, description, delai_souhaite, budget_estime, document_joint, statut) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'nouveau')",
                [$numero_devis, $nom, $email, $telephone, $localisation, $service, $type_service, $description, $delai_souhaite, $budget_estime, $document_joint]
            );
            
            if ($result) {
                // Envoyer un email de confirmation au client
                $subject = 'Demande de devis reçue - ' . $numero_devis;
                $body = "Bonjour $nom,\n\n";
                $body .= "Nous avons bien reçu votre demande de devis.\n\n";
                $body .= "Numéro de demande: $numero_devis\n";
                $body .= "Service: $service\n";
                $body .= "Localisation: $localisation\n\n";
                $body .= "Nous analyserons votre demande et vous contacterons bientôt pour vous proposer un devis détaillé.\n\n";
                $body .= "Cordialement,\n";
                $body .= "L'équipe KM Services\n";
                $body .= "contact@kmservices.cd\n";
                
                $headers = "From: contact@kmservices.cd\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                $headers .= "Reply-To: contact@kmservices.cd\r\n";
                
                @mail($email, $subject, $body, $headers);
                
                // Rediriger avec message de succès
                header('Location: ' . APP_URL . 'devis?success=1');
                exit;
            } else {
                error_log('Failed to insert quote request');
            }
        }
    } catch (Throwable $e) {
        error_log('Error in devis form: ' . $e->getMessage());
    }
}
$success = isset($_GET['success']);
$prefService = $_GET['service'] ?? '';
$err = isset($devisError) ? $devisError : '';
?>
<section class="container" style="max-width:900px; margin:2rem auto;">
  <h1>Demande de Devis</h1>
  <?php if ($success): ?>
    <div style="margin-top:1rem; padding:0.75rem; background:#d1fae5; color:#065f46; border-left:4px solid #10b981; border-radius:4px;">
      Votre demande a été envoyée. Nous vous contacterons rapidement.
    </div>
  <?php endif; ?>
  <?php if ($err): ?>
    <div style="margin-top:1rem; padding:0.75rem; background:#fee2e2; color:#7f1d1d; border-left:4px solid #ef4444; border-radius:4px;">
      Erreur: <?php echo htmlspecialchars($err); ?>
    </div>
  <?php endif; ?>
  <form action="<?php echo APP_URL; ?>devis" method="POST" enctype="multipart/form-data" style="margin-top:1rem;">
    <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
      <div>
        <label>Nom *</label>
        <input type="text" name="nom" required style="width:100%; padding:0.5rem;">
      </div>
      <div>
        <label>Email *</label>
        <input type="email" name="email" required style="width:100%; padding:0.5rem;">
      </div>
      <div>
        <label>Téléphone *</label>
        <input type="text" name="telephone" required style="width:100%; padding:0.5rem;">
      </div>
      <div>
        <label>Localisation *</label>
        <input type="text" name="localisation" required style="width:100%; padding:0.5rem;">
      </div>
      <div>
        <label>Service</label>
        <select name="service" id="devis_service" style="width:100%; padding:0.5rem;">
          <option value="general" <?php echo $prefService===''||$prefService==='general'?'selected':''; ?>>Général</option>
          <option value="construction" <?php echo $prefService==='construction'?'selected':''; ?>>Construction</option>
          <option value="forage" <?php echo $prefService==='forage'?'selected':''; ?>>Forage</option>
          <option value="plomberie" <?php echo $prefService==='plomberie'?'selected':''; ?>>Plomberie</option>
          <option value="peinture" <?php echo $prefService==='peinture'?'selected':''; ?>>Peinture</option>
          <option value="electrification" <?php echo $prefService==='electrification'?'selected':''; ?>>Électrification</option>
          <option value="materiels" <?php echo $prefService==='materiels'?'selected':''; ?>>Matériels de construction</option>
        </select>
      </div>
      <div id="forage_extra" style="display:none;">
        <label>Type de forage</label>
        <input type="text" name="type_forage" style="width:100%; padding:0.5rem;" placeholder="Puits d'eau, géotechnique, ...">
      </div>
      <div>
        <label>Délai souhaité</label>
        <input type="text" name="delai_souhaite" style="width:100%; padding:0.5rem;">
      </div>
      <div>
        <label>Budget estimé</label>
        <input type="text" name="budget_estime" style="width:100%; padding:0.5rem;">
      </div>
      <div style="grid-column:1 / -1;">
        <label>Description</label>
        <textarea name="description" rows="5" style="width:100%; padding:0.5rem;"></textarea>
      </div>
      <div style="grid-column:1 / -1;">
        <label>Document joint (PDF, JPG, PNG) – max 5 Mo</label>
        <input type="file" name="document_joint" accept=".pdf,image/*" style="width:100%; padding:0.5rem; background:#fff;">
      </div>
    </div>
    <button type="submit" style="margin-top:1rem; padding:0.75rem 1rem; background:#1e3a8a; color:#fff; border:none; border-radius:4px;">Envoyer la demande</button>
  </form>
</section>
<script>
function toggleForage() {
  const sel = document.getElementById('devis_service');
  const extra = document.getElementById('forage_extra');
  extra.style.display = sel.value === 'forage' ? 'block' : 'none';
}
document.getElementById('devis_service').addEventListener('change', toggleForage);
window.addEventListener('DOMContentLoaded', toggleForage);
</script>
