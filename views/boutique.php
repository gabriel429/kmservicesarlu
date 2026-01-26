<?php
// Assurer le chargement des produits et catégories si non fournis
if (!isset($products) || empty($products) || !isset($categories)) {
    if (!class_exists('MySQLCore')) {
        require_once dirname(__DIR__) . '/app/MySQL.php';
    }
    try {
        if (!isset($categories)) {
            $categories = MySQLCore::fetchAll(
                "SELECT id, nom, slug FROM product_categories WHERE actif = 1 ORDER BY nom ASC"
            );
        }
        if (!isset($products) || empty($products)) {
            $products = MySQLCore::fetchAll(
                "SELECT id, nom, slug, description, prix, stock, image_principale FROM products WHERE actif = 1 ORDER BY ordre ASC, created_at DESC LIMIT 50"
            );
        }
    } catch (Exception $e) {
        $products = $products ?? [];
        $categories = $categories ?? [];
    }
}
?>
<!-- Page Boutique -->
<section class="shop-page">
    <div class="container">
        <h1>Boutique En Ligne</h1>
        
        <div class="shop-container">
            <!-- Barre latérale filtres -->
            <aside class="shop-sidebar">
                <div class="filter-section">
                    <h3>Catégories</h3>
                    <ul>
                        <li><a href="<?php echo APP_URL; ?>boutique">Tous les produits</a></li>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <li><a href="<?php echo APP_URL; ?>boutique?cat=<?php echo htmlspecialchars($category['slug']); ?>"><?php echo htmlspecialchars($category['nom']); ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="filter-section">
                    <h3>Panier</h3>
                    <p><strong>Articles:</strong> <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></p>
                    <a href="<?php echo APP_URL; ?>panier" class="btn btn-primary btn-block">Voir le Panier</a>
                </div>
            </aside>
            
            <!-- Grille de produits -->
            <div class="products-section">
                <div class="products-grid">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <?php if (!empty($product['image_principale'])): ?>
                                        <img src="/img?p=uploads/<?php echo htmlspecialchars($product['image_principale']); ?>&w=400&h=200&q=85&format=webp" alt="<?php echo htmlspecialchars($product['nom']); ?>" loading="lazy" width="400" height="200"
                                             srcset="/img?p=uploads/<?php echo htmlspecialchars($product['image_principale']); ?>&w=200&h=200&q=85&format=webp 200w, /img?p=uploads/<?php echo htmlspecialchars($product['image_principale']); ?>&w=400&h=200&q=85&format=webp 400w"
                                             sizes="(max-width: 768px) 100vw, 400px">
                                    <?php else: ?>
                                        <img src="<?php echo ASSET_URL; ?>assets/images/placeholder_product.svg" alt="Image indisponible">
                                    <?php endif; ?>
                                    <?php if (isset($product['prix_promotion']) && !empty($product['prix_promotion'])): ?>
                                        <span class="product-badge">Promo</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-content">
                                    <h3><?php echo htmlspecialchars($product['nom']); ?></h3>
                                    <p class="product-description"><?php $desc = isset($product['description']) ? (string)$product['description'] : ''; echo htmlspecialchars(substr($desc, 0, 100)) . '...'; ?></p>
                                    <div class="product-price">
                                        <?php 
                                            $prix = isset($product['prix']) && $product['prix'] !== '' ? (float)$product['prix'] : 0.0;
                                            $promo = (isset($product['prix_promotion']) && $product['prix_promotion'] !== null && $product['prix_promotion'] !== '') ? (float)$product['prix_promotion'] : null;
                                            $hasPromo = $promo !== null && $prix > 0 && $promo < $prix;
                                        ?>
                                        <?php if ($hasPromo): ?>
                                            <span class="price-original">$<?php echo number_format($prix, 2); ?></span>
                                            <span class="price-promotion">$<?php echo number_format($promo, 2); ?></span>
                                        <?php else: ?>
                                            <span class="price">$<?php echo number_format($prix, 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-actions">
                                        <a href="<?php echo APP_URL; ?>produit/<?php echo htmlspecialchars($product['slug']); ?>" class="btn btn-secondary">Détails</a>
                                        <form method="POST" action="<?php echo APP_URL; ?>panier/ajouter" style="display:inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" class="btn btn-primary">Ajouter</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-products">Aucun produit disponible pour le moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
