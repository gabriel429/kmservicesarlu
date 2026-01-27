<!-- Détail Produit -->
<section class="product-detail-page">
    <div class="container">
        <?php 
        // Charger le produit depuis les données passées ou depuis la session
        $product = $_GET['_product'] ?? null;
        $products = $_GET['_products'] ?? [];
        
        if ($product): ?>
            <div class="breadcrumb">
                <a href="<?php echo APP_URL; ?>">Accueil</a> / 
                <a href="<?php echo APP_URL; ?>boutique">Boutique</a> / 
                <span><?php echo htmlspecialchars($product['nom']); ?></span>
            </div>

            <div class="product-detail-content">
                <div class="product-images">
                    <?php if (!empty($product['image_principale'])): ?>
                        <div class="main-image">
                            <?php $imgSrc = $product['image_principale']; $isUrl = is_string($imgSrc) && str_starts_with($imgSrc, 'http'); ?>
                            <?php if ($isUrl): ?>
                                <img src="/img?url=<?php echo urlencode($imgSrc); ?>&w=720&q=85&format=webp" id="mainImage" alt="<?php echo htmlspecialchars($product['nom']); ?>" width="720" style="height: auto; max-height: 500px; object-fit: contain;"
                                     srcset="/img?url=<?php echo urlencode($imgSrc); ?>&w=360&q=85&format=webp 360w, /img?url=<?php echo urlencode($imgSrc); ?>&w=720&q=85&format=webp 720w"
                                     sizes="(max-width: 768px) 100vw, 720px">
                            <?php else: ?>
                                <img src="/img?p=uploads/<?php echo htmlspecialchars($imgSrc); ?>&w=720&q=85&format=webp" id="mainImage" alt="<?php echo htmlspecialchars($product['nom']); ?>" width="720" style="height: auto; max-height: 500px; object-fit: contain;"
                                     srcset="/img?p=uploads/<?php echo htmlspecialchars($imgSrc); ?>&w=360&q=85&format=webp 360w, /img?p=uploads/<?php echo htmlspecialchars($imgSrc); ?>&w=720&q=85&format=webp 720w"
                                     sizes="(max-width: 768px) 100vw, 720px">
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="main-image">
                            <img src="<?php echo ASSET_URL; ?>assets/images/placeholder_product.svg" alt="Image indisponible">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="product-info">
                    <h1><?php echo htmlspecialchars($product['nom']); ?></h1>
                    
                    <?php if (isset($product['reference']) && $product['reference'] !== '' && $product['reference'] !== null): ?>
                        <p class="reference">Référence: <strong><?php echo htmlspecialchars($product['reference']); ?></strong></p>
                    <?php endif; ?>

                    <div class="price-section">
                        <?php 
                            $prix = isset($product['prix']) && $product['prix'] !== '' ? (float)$product['prix'] : 0.0;
                            $promo = (isset($product['prix_promotion']) && $product['prix_promotion'] !== null && $product['prix_promotion'] !== '') 
                                ? (float)$product['prix_promotion'] : null;
                            $hasPromo = $promo !== null && $prix > 0 && $promo < $prix;
                        ?>
                        <?php if ($hasPromo): ?>
                            <span class="price-original">$<?php echo number_format($prix, 2); ?></span>
                            <span class="price-promotion">$<?php echo number_format($promo, 2); ?></span>
                            <span class="discount-badge">-<?php echo round((1 - ($promo / $prix)) * 100); ?>%</span>
                        <?php else: ?>
                            <span class="price-main">$<?php echo number_format($prix, 2); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="stock-status">
                        <?php $stock = isset($product['stock']) ? (int)$product['stock'] : 0; ?>
                        <?php if ($stock > 0): ?>
                            <span class="in-stock"><i class="fas fa-check-circle"></i> En stock (<?php echo $stock; ?>)</span>
                        <?php else: ?>
                            <span class="out-of-stock"><i class="fas fa-times-circle"></i> Rupture de stock</span>
                        <?php endif; ?>
                    </div>

                    <div class="description">
                        <?php echo (isset($product['description']) && $product['description'] !== '') 
                            ? nl2br(htmlspecialchars($product['description'])) 
                            : 'Aucune description disponible'; ?>
                    </div>

                    <div class="quantity-selector" style="margin-bottom: 1rem;">
                        <label for="detailQuantite">Quantité:</label>
                        <input type="number" id="detailQuantite" min="1" value="1" <?php echo (isset($product['stock']) && (int)$product['stock'] <= 0) ? 'disabled' : ''; ?> style="width: 100px; padding: 0.5rem;">
                    </div>
                    
                    <button type="button" class="btn btn-primary btn-large" onclick="commanderProduitDetail(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['nom'])); ?>', <?php echo $product['prix']; ?>)" <?php echo (isset($product['stock']) && (int)$product['stock'] <= 0) ? 'disabled' : ''; ?>>
                        <i class="fas fa-shopping-cart"></i> Commander
                    </button>

                    <div class="product-meta">
                        <p><strong>SKU:</strong> <?php echo htmlspecialchars($product['reference'] ?? 'N/A'); ?></p>
                        <p><strong>Catégorie:</strong> 
                            <?php 
                            $category = isset($product['category_id']) ? 
                                $db->fetch("SELECT nom FROM product_categories WHERE id = ?", [$product['category_id']]) 
                                : null;
                            echo $category ? htmlspecialchars($category['nom']) : 'Non spécifiée';
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="related-products">
                <h2>Produits Similaires</h2>
                <div class="products-grid">
                    <?php if (!empty($products)): ?>
                        <?php $i = 0; foreach ($products as $p): 
                            if ($p['id'] !== $product['id'] && $i < 4):
                                $i++;
                        ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <?php if (!empty($p['image_principale'])): ?>
                                        <?php $rel = $p['image_principale']; $isUrl2 = is_string($rel) && str_starts_with($rel, 'http'); ?>
                                        <?php if ($isUrl2): ?>
                                            <img src="/img?url=<?php echo urlencode($rel); ?>&w=400&h=200&q=85&format=webp" alt="<?php echo htmlspecialchars($p['nom']); ?>" loading="lazy" width="400" height="200"
                                                 srcset="/img?url=<?php echo urlencode($rel); ?>&w=200&h=200&q=85&format=webp 200w, /img?url=<?php echo urlencode($rel); ?>&w=400&h=200&q=85&format=webp 400w"
                                                 sizes="(max-width: 768px) 100vw, 400px">
                                        <?php else: ?>
                                            <img src="/img?p=uploads/<?php echo htmlspecialchars($rel); ?>&w=400&h=200&q=85&format=webp" alt="<?php echo htmlspecialchars($p['nom']); ?>" loading="lazy" width="400" height="200"
                                                 srcset="/img?p=uploads/<?php echo htmlspecialchars($rel); ?>&w=200&h=200&q=85&format=webp 200w, /img?p=uploads/<?php echo htmlspecialchars($rel); ?>&w=400&h=200&q=85&format=webp 400w"
                                                 sizes="(max-width: 768px) 100vw, 400px">
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <img src="<?php echo ASSET_URL; ?>assets/images/placeholder_product.svg" alt="Image indisponible">
                                    <?php endif; ?>
                                </div>
                                <div class="product-content">
                                    <h3><?php echo htmlspecialchars($p['nom']); ?></h3>
                                    <div class="product-price">
                                        <?php $price = (isset($p['prix']) && $p['prix'] !== '') ? (float)$p['prix'] : 0.0; ?>
                                        <span class="price">$<?php echo number_format($price, 2); ?></span>
                                    </div>
                                    <a href="<?php echo APP_URL; ?>produit/<?php echo htmlspecialchars($p['slug']); ?>" class="btn btn-secondary">Voir</a>
                                </div>
                            </div>
                        <?php endif; endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <p>Produit non trouvé.</p>
        <?php endif; ?>
    </div>
</section>

<style>
.breadcrumb {
    margin-bottom: 2rem;
    color: var(--text-secondary);
}

.breadcrumb a {
    color: var(--primary-color);
    text-decoration: none;
}

.product-detail-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    margin-bottom: 3rem;
}

.product-images {
    background-color: var(--light-bg);
    padding: 2rem;
    border-radius: 0.5rem;
}

.main-image {
    width: 100%;
    margin-bottom: 1rem;
}

.main-image img {
    width: 100%;
    height: auto;
    border-radius: 0.5rem;
}

.product-info h1 {
    font-size: 2rem;
    margin-bottom: 1rem;
    color: var(--primary-color);
}

.reference {
    color: var(--text-secondary);
    margin-bottom: 1rem;
}

.price-section {
    display: flex;
    align-items: baseline;
    gap: 1rem;
    margin-bottom: 1rem;
}

.price-main,
.price-promotion {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
}

.price-original {
    font-size: 1.2rem;
    text-decoration: line-through;
    color: var(--text-secondary);
}

.discount-badge {
    background-color: #ef4444;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    font-weight: 600;
}

.stock-status {
    margin-bottom: 1.5rem;
}

.in-stock {
    color: var(--accent-color);
    font-weight: 600;
}

.out-of-stock {
    color: #ef4444;
    font-weight: 600;
}

.stock-status i {
    margin-right: 0.5rem;
}

.description {
    color: var(--text-secondary);
    line-height: 1.8;
    margin-bottom: 2rem;
}

.add-to-cart-form {
    margin-bottom: 2rem;
}

.quantity-selector {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1rem;
}

.quantity-selector label {
    font-weight: 600;
}

.quantity-selector input {
    width: 80px;
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    border-radius: 0.25rem;
}

.product-meta {
    background-color: var(--light-bg);
    padding: 1.5rem;
    border-radius: 0.5rem;
}

.product-meta p {
    margin-bottom: 0.75rem;
    color: var(--text-secondary);
}

.related-products {
    background-color: var(--light-bg);
    padding: 2rem;
    border-radius: 0.5rem;
}

.related-products h2 {
    text-align: center;
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .product-detail-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    .price-section {
        flex-wrap: wrap;
    }
}
</style>
<!-- Modal de commande (réutilisé) -->
<div id="commandeModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:white; padding:2rem; border-radius:8px; max-width:600px; width:90%; max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <h2 id="commandeTitle">Commander</h2>
            <button onclick="closeCommandeModal()" style="background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>
        </div>

        <form id="commandeForm">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <label>Nom *</label>
                    <input type="text" id="commandeNom" name="nom_client" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                </div>
                <div>
                    <label>Email *</label>
                    <input type="email" id="commandeEmail" name="email_client" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <label>Téléphone *</label>
                    <input type="tel" id="commandeTelephone" name="telephone_client" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                </div>
                <div>
                    <label>Quantité *</label>
                    <input type="number" id="commandeQuantite" name="quantite" value="1" min="1" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                </div>
            </div>

            <div style="margin-bottom:1rem;">
                <label>Adresse de livraison</label>
                <textarea id="commandeAdresse" name="adresse_livraison" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px; resize:vertical;" rows="3"></textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <label>Ville</label>
                    <input type="text" id="commandeVille" name="ville" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                </div>
                <div>
                    <label>Code postal</label>
                    <input type="text" id="commandeCodePostal" name="code_postal" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                </div>
            </div>

            <div id="commandeInfo" style="background:#f0f8ff; padding:1rem; border-radius:4px; margin-bottom:1rem;">
                <p><strong id="infoProduit">Produit sélectionné</strong></p>
                <p>Prix unitaire: <strong id="infoPrix">$0.00</strong></p>
                <p>Montant total: <strong id="infoTotal">$0.00</strong></p>
                <input type="hidden" id="commandeProductId" name="product_id">
            </div>

            <div style="display:flex; gap:1rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;">Confirmer la commande</button>
                <button type="button" onclick="closeCommandeModal()" class="btn btn-secondary" style="flex:1;">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
function commanderProduitDetail(productId, productNom, productPrix) {
    const quantite = parseInt(document.getElementById('detailQuantite').value) || 1;
    document.getElementById('commandeProductId').value = productId;
    document.getElementById('infoProduit').textContent = productNom;
    document.getElementById('infoPrix').textContent = '$' + parseFloat(productPrix).toFixed(2);
    
    // Calculer le montant total
    const total = parseFloat(productPrix) * quantite;
    document.getElementById('infoTotal').textContent = '$' + total.toFixed(2);
    document.getElementById('commandeQuantite').value = quantite;
    
    // Afficher le modal
    document.getElementById('commandeModal').style.display = 'flex';
    document.getElementById('commandeForm').reset();
    
    // Restore values
    document.getElementById('commandeQuantite').value = quantite;
}

function closeCommandeModal() {
    document.getElementById('commandeModal').style.display = 'none';
    document.getElementById('commandeForm').reset();
}

// Mettre à jour le montant total quand la quantité change
document.getElementById('commandeQuantite').addEventListener('change', function() {
    const prixText = document.getElementById('infoPrix').textContent;
    const prix = parseFloat(prixText.replace('$', ''));
    const quantite = parseInt(this.value) || 1;
    const total = prix * quantite;
    document.getElementById('infoTotal').textContent = '$' + total.toFixed(2);
});

// Traiter la soumission du formulaire de commande
document.getElementById('commandeForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'create');
    
    try {
        const response = await fetch('<?php echo ASSET_URL; ?>handlers/crud_orders.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Commande créée avec succès! N° de commande: ' + data.numero_commande);
            closeCommandeModal();
        } else {
            alert('Erreur: ' + data.message);
        }
    } catch (error) {
        alert('Erreur lors de la création de la commande');
        console.error(error);
    }
});

// Fermer le modal quand on clique en dehors
document.getElementById('commandeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCommandeModal();
    }
});
</script>