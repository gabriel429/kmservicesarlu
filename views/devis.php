<?php
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
