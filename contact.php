<?php
$page_title = 'Contact - KM SERVICES';
include __DIR__ . '/includes/header.php';
?>
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <h2 class="section-title">Contactez-nous</h2>
                <p class="text-muted">Nous répondons sous 24h.</p>
                <?php if (!empty($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-check-circle"></i> <strong>Message envoyé !</strong> Nous avons reçu votre message et vous répondrons dans les 24 heures.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <div class="card p-4 mb-4">
                    <h6 class="fw-bold">Clients & partenaires</h6>
                    <p class="text-muted">Particuliers, entreprises et institutions publiques nous font confiance pour des solutions intégrées et durables.</p>
                    <h6 class="fw-bold">Engagement</h6>
                    <p class="text-muted">Qualité, sécurité, innovation et respect de l’environnement guident chacune de nos interventions.</p>
                </div>
                <form method="post" action="api/contact.php" class="card p-4">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Numéro de téléphone</label>
                        <input type="tel" name="telephone" class="form-control" placeholder="+243..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Envoyer</button>
                </form>
            </div>
            <div class="col-md-6">
                <div class="card p-4 h-100">
                    <h5 class="fw-bold">Informations</h5>
                    <p><i class="fa-solid fa-phone"></i> +243 (0) 892 017 793 / 999 920 715</p>
                    <p><i class="fa-solid fa-envelope"></i> contact@kmservicesarlu.cd</p>
                    <p><i class="fa-solid fa-location-dot"></i>Avenue Kabalo N°235, Quartier Makutano, dans la com­mune de Lubumbashi</p>
                    <div class="ratio ratio-16x9 mt-3">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m16!1m12!1m3!1d1161.683654635354!2d27.480560721343636!3d-11.664441640676397!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!2m1!1s520%2C%20Avenue%20Kapenda%2CQuartier%20Makutano%2C%20Lubumbashi%2C%20Haut-Katanga%2C%20Rep.%20Dem.%20Congo!5e0!3m2!1sfr!2scd!4v1770088312294!5m2!1sfr!2scd" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
