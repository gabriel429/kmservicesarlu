<!-- Page Panier -->
<section class="cart-page">
    <div class="container">
        <h1>Mon Panier</h1>

        <div class="cart-content">
            <div class="cart-items">
                <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix</th>
                                <th>Quantité</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $total = 0; foreach ($_SESSION['cart'] as $item): ?>
                                <?php 
                                $subtotal = $item['prix'] * $item['quantite'];
                                $total += $subtotal;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['nom']); ?></td>
                                    <td>$<?php echo number_format($item['prix'], 2); ?></td>
                                    <td>
                                        <form method="POST" action="<?php echo APP_URL; ?>panier/update" style="display: inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <input type="number" name="quantite" value="<?php echo $item['quantite']; ?>" min="1" style="width: 60px;">
                                            <button type="submit" class="btn btn-small btn-primary">Mettre à jour</button>
                                        </form>
                                    </td>
                                    <td>$<?php echo number_format($subtotal, 2); ?></td>
                                    <td>
                                        <form method="POST" action="<?php echo APP_URL; ?>panier/supprimer" style="display: inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <button type="submit" class="btn btn-small btn-danger">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="empty-cart">Votre panier est vide. <a href="<?php echo APP_URL; ?>boutique">Continuer vos achats</a></p>
                <?php endif; ?>
            </div>

            <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                <div class="cart-summary">
                    <h2>Résumé de la Commande</h2>
                    
                    <div class="summary-item">
                        <span>Sous-total:</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span>Frais de livraison:</span>
                        <span>À calculer</span>
                    </div>
                    
                    <div class="summary-total">
                        <span>TOTAL:</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>

                    <form method="POST" action="<?php echo APP_URL; ?>commande/creer">
                        <h3>Informations de Livraison</h3>
                        
                        <div class="form-group">
                            <label for="nom">Nom Complet *</label>
                            <input type="text" id="nom" name="nom_client" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email_client" required>
                        </div>

                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone_client">
                        </div>

                        <div class="form-group">
                            <label for="adresse">Adresse de Livraison *</label>
                            <textarea id="adresse" name="adresse_livraison" rows="3" required></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="ville">Ville</label>
                                <input type="text" id="ville" name="ville">
                            </div>
                            <div class="form-group">
                                <label for="code">Code Postal</label>
                                <input type="text" id="code" name="code_postal">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-large btn-block">
                            Procéder au Paiement
                        </button>
                    </form>

                    <a href="<?php echo APP_URL; ?>boutique" class="btn btn-secondary btn-block" style="margin-top: 1rem;">
                        Continuer les achats
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
.cart-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.cart-table {
    width: 100%;
    border-collapse: collapse;
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: var(--shadow);
    overflow: hidden;
}

.cart-table th {
    background-color: var(--primary-color);
    color: white;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
}

.cart-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.cart-table tr:hover {
    background-color: var(--light-bg);
}

.empty-cart {
    background-color: white;
    padding: 2rem;
    text-align: center;
    border-radius: 0.5rem;
    box-shadow: var(--shadow);
}

.empty-cart a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
}

.empty-cart a:hover {
    text-decoration: underline;
}

.cart-summary {
    background-color: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: var(--shadow);
    height: fit-content;
}

.cart-summary h2 {
    text-align: left;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.cart-summary h3 {
    text-align: left;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    font-size: 1rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-color);
}

.summary-total {
    display: flex;
    justify-content: space-between;
    padding: 1rem 0;
    font-weight: 700;
    font-size: 1.2rem;
    color: var(--primary-color);
}

.btn-block {
    width: 100%;
}

@media (max-width: 768px) {
    .cart-content {
        grid-template-columns: 1fr;
    }

    .cart-summary {
        height: auto;
    }
}
</style>
