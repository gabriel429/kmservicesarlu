<!-- Admin - Formulaire de Connexion -->

<main style="position: relative; display: flex; align-items: center; justify-content: center; overflow: hidden;">
    <!-- Image de fond -->
    <img src="<?php echo ASSET_URL; ?>assets/images/login.jpeg" alt="Login Background" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1;">
    
    <!-- Overlay semi-transparent pour améliorer la lisibilité -->
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.4); z-index: 2;"></div>
    
    <div style="background-color: white; padding: 3rem; border-radius: 8px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); max-width: 400px; width: 100%; position: relative; z-index: 10;">
        <h1 style="color: #1e3a8a; margin-bottom: 0.5rem; font-size: 1.8rem; text-align: center;">KM Services</h1>
        <p style="color: #6b7280; margin-bottom: 2rem; text-align: center;">Connexion Administrateur</p>

        <?php if (isset($error)): ?>
            <div style="padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px; background-color: #fee2e2; color: #7f1d1d; border-left: 4px solid #ef4444;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form id="loginForm" method="POST" style="display: none;">
            <div style="margin-bottom: 1.5rem;">
                <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1e3a8a;">Nom d'utilisateur *</label>
                <input type="text" id="username" name="username" required style="width: 100%; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 1rem;">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1e3a8a;">Mot de passe *</label>
                <input type="password" id="password" name="password" required style="width: 100%; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 1rem;">
            </div>

            <button type="submit" style="width: 100%; padding: 0.75rem; background-color: #1e3a8a; color: white; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; font-size: 1rem; transition: all 0.3s ease;">Se Connecter</button>
        </form>

        <form id="loginFormAjax">
            <div style="margin-bottom: 1.5rem;">
                <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1e3a8a;">Nom d'utilisateur *</label>
                <input type="text" id="username" name="username" required style="width: 100%; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 1rem;">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1e3a8a;">Mot de passe *</label>
                <input type="password" id="password" name="password" required style="width: 100%; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 1rem;">
            </div>

            <button id="loginSubmit" type="submit" style="width: 100%; padding: 0.75rem; background-color: #1e3a8a; color: white; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; font-size: 1rem; transition: all 0.3s ease;">Se Connecter (AJAX)</button>
        </form>

        <div id="loginMsg" style="display:none; margin-top:1rem; padding:0.75rem; border-radius:4px; border-left:4px solid;">
        </div>

        <a href="<?php echo APP_URL; ?>" style="display: block; margin-top: 1rem; text-align: center; color: #1e3a8a; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Retour au site
        </a>

        <a href="#" id="toggleClassic" style="display:block; margin-top:0.5rem; text-align:center; color:#1e3a8a; text-decoration: underline;">
            Problème de connexion ? Utiliser le formulaire classique
        </a>
    </div>
</main>

<style>
    input[type="text"]:focus,
    input[type="password"]:focus {
        outline: none;
        border-color: #1e3a8a;
        box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
    }
    
    button[type="submit"]:hover {
        background-color: #1e40af;
    }
</style>

<script>
document.getElementById('loginFormAjax').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const username = document.querySelector('#loginFormAjax input[name="username"]').value;
    const password = document.querySelector('#loginFormAjax input[name="password"]').value;
    const submitBtn = document.getElementById('loginSubmit');
    const msgBox = document.getElementById('loginMsg');
    const showMsg = (text, type) => {
        msgBox.style.display = 'block';
        msgBox.textContent = text;
        msgBox.style.color = type === 'error' ? '#7f1d1d' : '#065f46';
        msgBox.style.backgroundColor = type === 'error' ? '#fee2e2' : '#d1fae5';
        msgBox.style.borderLeftColor = type === 'error' ? '#ef4444' : '#10b981';
    };
    
    try {
        console.log('Tentative de connexion avec:', { username, password });
        submitBtn.disabled = true;
        submitBtn.textContent = 'Connexion...';
        msgBox.style.display = 'none';

        const response = await fetch('<?php echo ASSET_URL; ?>handlers/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                username: username,
                password: password
            })
        });
        
        console.log('Statut de la réponse:', response.status);
        console.log('Type de contenu:', response.headers.get('content-type'));
        const data = await response.json().catch(async () => {
            const txt = await response.text();
            console.error('Réponse brute (non-JSON):', txt);
            throw new Error('Réponse non-JSON du serveur');
        });
        console.log('Données JSON parsées:', data);
        
        if (data.success) {
            showMsg('Connexion réussie, redirection...', 'success');
            const redirect = data.redirect ? ('<?php echo APP_URL; ?>'.replace(/\/?$/, '/') + data.redirect.replace(/^\//, '')) : '<?php echo APP_URL; ?>admin/dashboard';
            window.location.href = redirect;
        } else {
            showMsg(data.message || 'Identifiant ou mot de passe incorrect', 'error');
        }
    } catch (error) {
        console.error('Erreur de fetch:', error);
        showMsg('Erreur de connexion réseau', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Se Connecter (AJAX)';
    }
});

// Bascule vers formulaire classique en cas de soucis AJAX
document.getElementById('toggleClassic').addEventListener('click', function(e){
    e.preventDefault();
    const classic = document.getElementById('loginForm');
    const ajax = document.getElementById('loginFormAjax');
    if (classic && ajax) {
        classic.style.display = 'block';
        ajax.style.display = 'none';
        const msgBox = document.getElementById('loginMsg');
        if (msgBox) msgBox.style.display = 'none';
    }
});
</script>
