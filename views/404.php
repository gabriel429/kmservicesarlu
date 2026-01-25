<!-- Page 404 -->
<section class="page-404">
    <div class="container">
        <div class="error-container">
            <h1>404</h1>
            <h2>Page non trouvée</h2>
            <!-- URI: <?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? ''); ?> -->
            <p>Désolé, la page que vous recherchez n'existe pas ou a été supprimée.</p>
            <div class="error-actions">
                <a href="<?php echo APP_URL; ?>" class="btn btn-primary">Retour à l'accueil</a>
                <a href="<?php echo APP_URL; ?>contact" class="btn btn-secondary">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<style>
.page-404 {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 80vh;
}

.error-container {
    text-align: center;
}

.error-container h1 {
    font-size: 6rem;
    color: var(--secondary-color);
    margin-bottom: 0;
}

.error-container h2 {
    font-size: 2rem;
    color: var(--primary-color);
    margin-top: 0;
    margin-bottom: 1rem;
}

.error-container p {
    color: var(--text-secondary);
    font-size: 1.1rem;
    margin-bottom: 2rem;
}

.error-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.error-actions a {
    padding: 0.75rem 1.5rem;
}
</style>
