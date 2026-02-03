<?php
$page_title = 'Demande de devis - KM SERVICES';
include __DIR__ . '/includes/header.php';
$services = getPDO()->query('SELECT * FROM services')->fetchAll();
?>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Demande de devis</h2>
            <p class="text-muted">Décrivez votre besoin en quelques étapes.</p>
        </div>
        <form class="card p-4" method="post" action="api/create_devis.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <div class="form-step active" data-step="1">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nom complet</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Service</label>
                        <select name="service_id" class="form-select" required>
                            <option value="">Choisir...</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= e($service['id']); ?>"><?= e($service['nom_service']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="text-end mt-4">
                    <button type="button" class="btn btn-primary" data-step="2">Continuer</button>
                </div>
            </div>
            <div class="form-step" data-step="2">
                <div class="mb-3">
                    <label class="form-label">Description du projet</label>
                    <textarea name="description" class="form-control" rows="5" required></textarea>
                </div>
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-step="1">Retour</button>
                    <button type="submit" class="btn btn-primary">Envoyer la demande</button>
                </div>
            </div>
        </form>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
