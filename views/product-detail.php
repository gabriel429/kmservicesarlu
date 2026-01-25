<!-- Détail Produit -->
<section class="product-detail-page">
    <div class="container">
        <?php if (isset($product)): ?>
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
                                <img src="<?php echo APP_URL; ?>img?url=<?php echo urlencode($imgSrc); ?>&w=720&h=360&q=85&format=webp" id="mainImage" alt="<?php echo htmlspecialchars($product['nom']); ?>" width="720" height="360"
                                     srcset="<?php echo APP_URL; ?>img?url=<?php echo urlencode($imgSrc); ?>&w=360&h=360&q=85&format=webp 360w, <?php echo APP_URL; ?>img?url=<?php echo urlencode($imgSrc); ?>&w=720&h=360&q=85&format=webp 720w"
                                     sizes="(max-width: 768px) 100vw, 720px">
                            <?php else: ?>
                                <img src="<?php echo APP_URL; ?>img?p=uploads/<?php echo htmlspecialchars($imgSrc); ?>&w=720&h=360&q=85&format=webp" id="mainImage" alt="<?php echo htmlspecialchars($product['nom']); ?>" width="720" height="360"
                                     srcset="<?php echo APP_URL; ?>img?p=uploads/<?php echo htmlspecialchars($imgSrc); ?>&w=360&h=360&q=85&format=webp 360w, <?php echo APP_URL; ?>img?p=uploads/<?php echo htmlspecialchars($imgSrc); ?>&w=720&h=360&q=85&format=webp 720w"
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

                    <form method="POST" action="<?php echo APP_URL; ?>panier/ajouter" class="add-to-cart-form">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <div class="quantity-selector">
                            <label for="quantity">Quantité:</label>
                            <input type="number" id="quantity" name="quantity" min="1" value="1" <?php echo (isset($product['stock']) && (int)$product['stock'] <= 0) ? 'disabled' : ''; ?>>
                        </div>
                        <button type="submit" class="btn btn-primary btn-large" <?php echo (isset($product['stock']) && (int)$product['stock'] <= 0) ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i> Ajouter au Panier
                        </button>
                    </form>

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
                                            <img src="<?php echo APP_URL; ?>img?url=<?php echo urlencode($rel); ?>&w=400&h=200&q=85&format=webp" alt="<?php echo htmlspecialchars($p['nom']); ?>" loading="lazy" width="400" height="200"
                                                 srcset="<?php echo APP_URL; ?>img?url=<?php echo urlencode($rel); ?>&w=200&h=200&q=85&format=webp 200w, <?php echo APP_URL; ?>img?url=<?php echo urlencode($rel); ?>&w=400&h=200&q=85&format=webp 400w"
                                                 sizes="(max-width: 768px) 100vw, 400px">
                                        <?php else: ?>
                                            <img src="<?php echo APP_URL; ?>img?p=uploads/<?php echo htmlspecialchars($rel); ?>&w=400&h=200&q=85&format=webp" alt="<?php echo htmlspecialchars($p['nom']); ?>" loading="lazy" width="400" height="200"
                                                 srcset="<?php echo APP_URL; ?>img?p=uploads/<?php echo htmlspecialchars($rel); ?>&w=200&h=200&q=85&format=webp 200w, <?php echo APP_URL; ?>img?p=uploads/<?php echo htmlspecialchars($rel); ?>&w=400&h=200&q=85&format=webp 400w"
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
