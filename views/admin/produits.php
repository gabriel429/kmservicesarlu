<?php
/**
 * Page d'Administration des Produits
 */
?>

<div class="admin-section">
    <div class="section-header">
        <h2>Gestion des Produits</h2>
        <button class="btn btn-primary" onclick="openAddProductModal()">+ Ajouter un Produit</button>
    </div>
    
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    if (!class_exists('MySQLCore')) {
                        require_once dirname(__DIR__, 2) . '/app/MySQL.php';
                    }
                    $products = MySQLCore::fetchAll(
                        "SELECT p.id, p.nom, p.prix, p.stock, p.actif FROM products p ORDER BY p.created_at DESC"
                    );
                    
                    if (!empty($products)):
                        foreach ($products as $product):
                            $statusBadge = $product['actif'] ? 'badge-success' : 'badge-danger';
                            $statusText = $product['actif'] ? 'Actif' : 'Inactif';
                ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($product['nom']); ?></strong></td>
                                <td><?php echo htmlspecialchars($product['prix']); ?> USD</td>
                                <td><?php echo htmlspecialchars($product['stock'] ?? 0); ?></td>
                                <td><span class="badge <?php echo $statusBadge; ?>"><?php echo $statusText; ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="editProduct(<?php echo $product['id']; ?>)">Éditer</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)">Supprimer</button>
                                </td>
                            </tr>
                <?php
                        endforeach;
                    else:
                ?>
                        <tr>
                            <td colspan="5" class="text-center">Aucun produit trouvé</td>
                        </tr>
                <?php
                    endif;
                } catch (Exception $e) {
                    echo '<tr><td colspan="5" class="text-center alert alert-danger">Erreur de chargement: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .admin-section {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    
    .table-container {
        overflow-x: auto;
    }
    
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95rem;
    }
    
    .admin-table thead {
        background-color: #f5f5f5;
    }
    
    .admin-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #e0e0e0;
    }
    
    .admin-table td {
        padding: 12px;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .admin-table tbody tr:hover {
        background-color: #f9f9f9;
    }
    
    .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .badge-success {
        background-color: #d4edda;
        color: #155724;
    }
    
    .badge-danger {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .btn-sm {
        padding: 6px 12px;
        font-size: 0.85rem;
    }
    
    .btn-info {
        background-color: #17a2b8;
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 4px;
    }
    
    .btn-info:hover {
        background-color: #138496;
    }
</style>

<script>
    function openAddProductModal() {
        document.getElementById('productForm').reset();
        document.getElementById('productId').value = '';
        document.getElementById('modalTitle').textContent = 'Ajouter un Produit';
        document.getElementById('productModal').style.display = 'flex';
    }
    
    function closeProductModal() {
        document.getElementById('productModal').style.display = 'none';
    }
    
    function editProduct(id) {
        fetch('/kmservices/public/handlers/crud_products.php?action=get&id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('productId').value = data.data.id;
                    document.getElementById('productNom').value = data.data.nom;
                    document.getElementById('productDescription').value = data.data.description;
                    document.getElementById('productPrix').value = data.data.prix;
                    document.getElementById('productStock').value = data.data.stock;
                    document.getElementById('productActif').checked = data.data.actif;
                    document.getElementById('modalTitle').textContent = 'Éditer le Produit';
                    document.getElementById('productModal').style.display = 'flex';
                }
            });
    }
    
    function deleteProduct(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            
            fetch('/kmservices/public/handlers/crud_products.php', {method: 'POST', body: formData})
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                });
        }
    }
    
    document.getElementById('productForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('productId').value;
        const action = id ? 'update' : 'create';
        
        const formData = new FormData();
        formData.append('action', action);
        formData.append('id', id);
        formData.append('nom', document.getElementById('productNom').value);
        formData.append('description', document.getElementById('productDescription').value);
        formData.append('prix', document.getElementById('productPrix').value);
        formData.append('stock', document.getElementById('productStock').value);
        formData.append('actif', document.getElementById('productActif').checked ? 1 : 0);
        
        // Ajouter l'image si elle existe
        const imageFile = document.getElementById('productImage').files[0];
        if (imageFile) {
            formData.append('image', imageFile);
        }
        
        fetch('/kmservices/public/handlers/crud_products.php', {method: 'POST', body: formData})
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeProductModal();
                    location.reload();
                } else {
                    alert('Erreur: ' + data.message);
                }
            });
    });
    
    window.onclick = function(event) {
        const modal = document.getElementById('productModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>

<!-- Modal de Formulaire de Produit -->
<div id="productModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 id="modalTitle">Ajouter un Produit</h2>
            <button onclick="closeProductModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        
        <form id="productForm" enctype="multipart/form-data">
            <input type="hidden" id="productId">
            
            <div style="margin-bottom: 1rem;">
                <label for="productNom" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Nom du Produit *</label>
                <input type="text" id="productNom" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label for="productDescription" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Description</label>
                <textarea id="productDescription" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label for="productPrix" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Prix (USD)</label>
                    <input type="number" id="productPrix" step="0.01" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label for="productStock" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Stock</label>
                    <input type="number" id="productStock" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label for="productImage" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Image du Produit</label>
                <input type="file" id="productImage" accept="image/*" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <small style="color: #666;">JPG, PNG, GIF (max 5MB)</small>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" id="productActif" style="margin-right: 0.5rem;">
                    <span>Actif</span>
                </label>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="closeProductModal()" style="padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">Annuler</button>
                <button type="submit" style="padding: 8px 16px; background: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
