<?php
/**
 * Page de Paramètres Admin
 */
?>

<div class="admin-section">
    <h2>Paramètres du Site</h2>
    
    <form class="settings-form">
        <div class="form-group">
            <label for="site_name">Nom du Site</label>
            <input type="text" id="site_name" name="site_name" value="KM Services">
        </div>
        
        <div class="form-group">
            <label for="site_email">Email de Contact</label>
            <input type="email" id="site_email" name="site_email" value="contact@kmservices.cd">
        </div>
        
        <div class="form-group">
            <label for="site_phone">Téléphone</label>
            <input type="tel" id="site_phone" name="site_phone" value="+243 892 017 793">
        </div>
        
        <div class="form-group">
            <label for="site_address">Adresse</label>
            <textarea id="site_address" name="site_address" rows="3">235, Kabalo, Q/Makutano, C/Lubumbashi, Haut-Katanga, RD Congo</textarea>
        </div>
        
        <div class="form-group">
            <label for="site_description">Description du Site</label>
            <textarea id="site_description" name="site_description" rows="5">KM Services est une entreprise spécialisée dans la construction, le forage et les matériels de construction.</textarea>
        </div>
        
        <div class="form-group">
            <label for="site_keywords">Mots-clés SEO</label>
            <input type="text" id="site_keywords" name="site_keywords" value="construction, forage, matériels">
        </div>
        
        <button type="submit" class="btn btn-primary">Enregistrer les Paramètres</button>
    </form>
</div>

<style>
    .admin-section {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        max-width: 600px;
    }
    
    .settings-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
    }
    
    .form-group label {
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
    }
    
    .form-group input,
    .form-group textarea {
        padding: 10px 12px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        font-family: inherit;
        font-size: 1rem;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #17a2b8;
        box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.1);
    }
</style>

<script>
    document.querySelector('.settings-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'save_settings');
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Enregistrement...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('<?php echo APP_URL; ?>handlers/crud_settings.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('✓ Paramètres enregistrés avec succès!');
            } else {
                alert('✗ Erreur: ' + (result.message || 'Impossible de sauvegarder'));
            }
        } catch (error) {
            alert('✗ Erreur: ' + error.message);
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });
</script>
