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
            // Vérifier si un filtre de catégorie est appliqué
            $categoryFilter = '';
            $params = [];
            
            if (isset($_GET['cat']) && !empty($_GET['cat'])) {
                $catSlug = $_GET['cat'];
                $categoryData = MySQLCore::fetch(
                    "SELECT id FROM product_categories WHERE slug = ? AND actif = 1",
                    [$catSlug]
                );
                if ($categoryData) {
                    $categoryFilter = ' AND category_id = ?';
                    $params[] = $categoryData['id'];
                }
            }
            
            $products = MySQLCore::fetchAll(
                "SELECT id, nom, slug, description, prix, stock, image_principale FROM products WHERE actif = 1" . $categoryFilter . " ORDER BY ordre ASC, created_at DESC LIMIT 50",
                $params
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
                                        <button type="button" class="btn btn-primary" onclick="commanderProduit(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['nom']); ?>', <?php echo $product['prix']; ?>)">Commander</button>
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
<!-- Modal de commande -->
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
function commanderProduit(productId, productNom, productPrix) {
    document.getElementById('commandeProductId').value = productId;
    document.getElementById('infoProduit').textContent = productNom;
    document.getElementById('infoPrix').textContent = '$' + parseFloat(productPrix).toFixed(2);
    
    // Calculer le montant total
    const quantite = parseInt(document.getElementById('commandeQuantite').value) || 1;
    const total = parseFloat(productPrix) * quantite;
    document.getElementById('infoTotal').textContent = '$' + total.toFixed(2);
    
    // Afficher le modal
    document.getElementById('commandeModal').style.display = 'flex';
    document.getElementById('commandeForm').reset();
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
            // Optionnel: rediriger vers une page de confirmation
            // window.location.href = '<?php echo APP_URL; ?>commande/' + data.order_id;
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