<?php
$page_title = 'Produits - KM SERVICES';
include __DIR__ . '/includes/header.php';
$products = getPDO()->query('SELECT * FROM produits ORDER BY date_creation DESC')->fetchAll();
?>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Catalogue produits</h2>
            <p class="text-muted">Commandez vos matériaux BTP via WhatsApp.</p>
        </div>
        <div class="row g-4">
            <?php if (!$products): ?>
                <div class="col-12 text-center text-muted">Aucun produit enregistré.</div>
            <?php endif; ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-4">
                    <div class="card card-product h-100">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?= e($product['image']); ?>" class="card-img-top" alt="<?= e($product['nom_produit']); ?>">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="fw-bold"><?= e($product['nom_produit']); ?></h5>
                            <p class="text-muted"><?= e($product['description']); ?></p>
                            <p class="fw-bold text-primary"><?= format_price((float) $product['prix']); ?></p>
                            <button
                                class="btn btn-primary mt-auto"
                                data-order
                                data-product-id="<?= e($product['id']); ?>"
                                data-product-name="<?= e($product['nom_produit']); ?>">
                                Commander via WhatsApp
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="orderForm">
                <div class="modal-header">
                    <h5 class="modal-title">Commande WhatsApp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="productId" name="product_id">
                    <div class="mb-3">
                        <label class="form-label">Produit</label>
                        <input type="text" id="productName" class="form-control" name="product_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nom complet</label>
                        <input type="text" class="form-control" name="nom" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="text" class="form-control" name="telephone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantité</label>
                        <input type="number" class="form-control" name="quantite" min="1" value="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="message" rows="3" placeholder="Précisions supplémentaires"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Envoyer sur WhatsApp</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
