<?php
$admin_title = 'Paramètres';
include __DIR__ . '/../includes/admin_header.php';

if (($_SESSION['user']['role'] ?? 'editor') === 'editor') {
    redirect(SITE_URL . '/admin/dashboard.php');
}

$pdo = getPDO();
$message = null;

// Vérifier si la table existe, sinon la créer
try {
    $pdo->query('SELECT 1 FROM parametres LIMIT 1');
} catch (PDOException $e) {
    // Créer la table si elle n'existe pas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS parametres (
            id INT PRIMARY KEY AUTO_INCREMENT,
            cle VARCHAR(100) UNIQUE NOT NULL,
            valeur TEXT,
            description VARCHAR(255),
            date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Insérer les valeurs par défaut
    $defaults = [
        ['site_nom', 'KM SERVICES SARL', 'Nom du site'],
        ['site_email', 'contact@kmservicesarlu.cd', 'Email principal'],
        ['site_telephone', '+243 (0) 892 017 793 / 999 920 715', 'Téléphone'],
        ['site_adresse', 'Avenue Kabalo N°235, Quartier Makutano, Lubumbashi, RDC', 'Adresse complète'],
        ['whatsapp_number', '243892017793', 'Numéro WhatsApp (format international sans +)'],
        ['facebook_url', 'https://web.facebook.com/me/', 'Lien Facebook'],
        ['instagram_url', '#', 'Lien Instagram'],
        ['linkedin_url', '#', 'Lien LinkedIn'],
        ['site_maintenance', '0', 'Mode maintenance (0=non, 1=oui)'],
    ];
    
    $stmt = $pdo->prepare('INSERT INTO parametres (cle, valeur, description) VALUES (?, ?, ?)');
    foreach ($defaults as $param) {
        $stmt->execute($param);
    }
}

if (is_post()) {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $message = 'Session expirée.';
    } else {
        foreach ($_POST as $key => $value) {
            if ($key === 'csrf_token') continue;
            
            $stmt = $pdo->prepare('UPDATE parametres SET valeur = ? WHERE cle = ?');
            $stmt->execute([trim($value), $key]);
        }
        
        $message = 'Paramètres mis à jour avec succès.';
        log_activity('Modification paramètres', null);
    }
}

$parametres = $pdo->query('SELECT * FROM parametres ORDER BY id ASC')->fetchAll();
$params = [];
foreach ($parametres as $param) {
    $params[$param['cle']] = $param['valeur'];
}
?>

<h2 class="fw-bold mb-4">Paramètres du site</h2>

<?php if ($message): ?>
    <div class="alert alert-info"><?= e($message); ?></div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
    
    <div class="card p-4 mb-4">
        <h5 class="fw-bold mb-3">Informations générales</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nom du site</label>
                <input type="text" name="site_nom" class="form-control" value="<?= e($params['site_nom'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email principal</label>
                <input type="email" name="site_email" class="form-control" value="<?= e($params['site_email'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Téléphone</label>
                <input type="text" name="site_telephone" class="form-control" value="<?= e($params['site_telephone'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Numéro WhatsApp <small class="text-muted">(format: 243XXXXXXXXX)</small></label>
                <input type="text" name="whatsapp_number" class="form-control" value="<?= e($params['whatsapp_number'] ?? ''); ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Adresse complète</label>
                <textarea name="site_adresse" class="form-control" rows="2"><?= e($params['site_adresse'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>

    <div class="card p-4 mb-4">
        <h5 class="fw-bold mb-3">Réseaux sociaux</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label"><i class="fa-brands fa-facebook"></i> Facebook</label>
                <input type="url" name="facebook_url" class="form-control" value="<?= e($params['facebook_url'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label"><i class="fa-brands fa-instagram"></i> Instagram</label>
                <input type="url" name="instagram_url" class="form-control" value="<?= e($params['instagram_url'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label"><i class="fa-brands fa-linkedin"></i> LinkedIn</label>
                <input type="url" name="linkedin_url" class="form-control" value="<?= e($params['linkedin_url'] ?? ''); ?>">
            </div>
        </div>
    </div>

    <div class="card p-4 mb-4">
        <h5 class="fw-bold mb-3">Options avancées</h5>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="site_maintenance" value="1" id="maintenance" <?= ($params['site_maintenance'] ?? '0') === '1' ? 'checked' : ''; ?>>
            <label class="form-check-label" for="maintenance">
                Mode maintenance (le site sera inaccessible aux visiteurs)
            </label>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
</form>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
