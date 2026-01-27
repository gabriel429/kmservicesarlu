<?php
/**
 * Page d'Administration des Commandes
 */
?>

<div class="admin-section">
    <h2>Gestion des Commandes</h2>
    
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>N° Commande</th>
                    <th>Client</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    if (!class_exists('MySQLCore')) {
                        require_once dirname(__DIR__, 2) . '/app/MySQL.php';
                    }
                    $orders = MySQLCore::fetchAll(
                        "SELECT id, numero_commande, nom_client, email_client, telephone_client, montant_total, statut, created_at FROM orders ORDER BY created_at DESC LIMIT 100"
                    );
                    
                    if (!empty($orders)):
                        foreach ($orders as $order):
                            $statusBadge = $order['statut'] === 'livree' ? 'badge-success' : ($order['statut'] === 'nouvelle' ? 'badge-warning' : 'badge-info');
                ?>
                            <tr>
                                <td data-label="N°"><strong><?php echo htmlspecialchars($order['numero_commande'] ?? ''); ?></strong></td>
                                <td data-label="Client"><?php echo htmlspecialchars($order['nom_client'] ?? ''); ?></td>
                                <td data-label="Email"><a href="mailto:<?php echo htmlspecialchars($order['email_client'] ?? ''); ?>"><?php echo htmlspecialchars($order['email_client'] ?? ''); ?></a></td>
                                <td data-label="Téléphone"><a href="tel:<?php echo htmlspecialchars($order['telephone_client'] ?? ''); ?>"><?php echo htmlspecialchars($order['telephone_client'] ?? 'N/A'); ?></a></td>
                                <td data-label="Montant"><?php echo htmlspecialchars($order['montant_total'] ?? ''); ?> USD</td>
                                <td data-label="Statut"><span class="badge <?php echo $statusBadge; ?>"><?php echo htmlspecialchars($order['statut'] ?? ''); ?></span></td>
                                <td data-label="Date"><?php echo $order['created_at'] ? date('d/m/Y H:i', strtotime($order['created_at'])) : 'N/A'; ?></td>
                                <td data-label="Actions">
                                    <button class="btn btn-sm btn-info" onclick="viewOrder(<?php echo $order['id']; ?>)">Voir</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteOrder(<?php echo $order['id']; ?>)">Supprimer</button>
                                </td>
                            </tr>
                <?php
                        endforeach;
                    else:
                ?>
                        <tr>
                            <td colspan="8" class="text-center">Aucune commande</td>
                        </tr>
                <?php
                    endif;
                } catch (Exception $e) {
                    echo '<tr><td colspan="8" class="text-center alert alert-danger">Erreur: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function viewOrder(id) {
        fetch(ASSET_URL + 'handlers/crud_orders.php?action=get&id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const order = data.data;
                    const items = data.items || [];
                    
                    let itemsHtml = '';
                    items.forEach(item => {
                        itemsHtml += `
                            <tr>
                                <td>${item.nom_produit}</td>
                                <td>${item.quantite}</td>
                                <td>$${parseFloat(item.prix_unitaire).toFixed(2)}</td>
                                <td>$${parseFloat(item.montant).toFixed(2)}</td>
                            </tr>
                        `;
                    });
                    
                    const modal = `
                        <div style="background:white; padding:2rem; border-radius:8px; max-width:700px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                                <h2>Commande ${order.numero_commande}</h2>
                                <button onclick="document.getElementById('orderModal').style.display='none';" style="background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>
                            </div>
                            
                            <div style="background:#f9f9f9; padding:1rem; border-radius:4px; margin-bottom:1.5rem;">
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                                    <div>
                                        <p><strong>Client:</strong> ${order.nom_client}</p>
                                        <p><strong>Email:</strong> <a href="mailto:${order.email_client}">${order.email_client}</a></p>
                                        <p><strong>Téléphone:</strong> <a href="tel:${order.telephone_client}">${order.telephone_client}</a></p>
                                    </div>
                                    <div>
                                        <p><strong>Date:</strong> ${new Date(order.created_at).toLocaleString('fr-FR')}</p>
                                        <p><strong>Montant:</strong> $${parseFloat(order.montant_total).toFixed(2)}</p>
                                        <p><strong>Statut:</strong> <span class="badge badge-${order.statut === 'livree' ? 'success' : order.statut === 'nouvelle' ? 'warning' : 'info'}">${order.statut}</span></p>
                                    </div>
                                </div>
                            </div>
                            
                            <h3>Articles</h3>
                            <table style="width:100%; border-collapse:collapse; margin-bottom:1.5rem;">
                                <thead>
                                    <tr style="background:#f0f0f0;">
                                        <th style="padding:0.5rem; text-align:left; border-bottom:1px solid #ddd;">Produit</th>
                                        <th style="padding:0.5rem; text-align:center; border-bottom:1px solid #ddd;">Quantité</th>
                                        <th style="padding:0.5rem; text-align:right; border-bottom:1px solid #ddd;">Prix unitaire</th>
                                        <th style="padding:0.5rem; text-align:right; border-bottom:1px solid #ddd;">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsHtml || '<tr><td colspan="4" style="padding:1rem; text-align:center;">Aucun article</td></tr>'}
                                </tbody>
                            </table>
                            
                            ${order.adresse_livraison ? `
                                <h3>Adresse de livraison</h3>
                                <p>${order.adresse_livraison}</p>
                                ${order.ville ? `<p>${order.ville}${order.code_postal ? ' ' + order.code_postal : ''}</p>` : ''}
                            ` : ''}
                            
                            <div style="display:flex; gap:1rem; margin-top:1.5rem;">
                                <select id="orderStatus" style="flex:1; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                                    <option value="nouvelle" ${order.statut === 'nouvelle' ? 'selected' : ''}>Nouvelle</option>
                                    <option value="confirmee" ${order.statut === 'confirmee' ? 'selected' : ''}>Confirmée</option>
                                    <option value="preparee" ${order.statut === 'preparee' ? 'selected' : ''}>Préparée</option>
                                    <option value="livree" ${order.statut === 'livree' ? 'selected' : ''}>Livrée</option>
                                    <option value="annulee" ${order.statut === 'annulee' ? 'selected' : ''}>Annulée</option>
                                </select>
                                <button class="btn btn-primary" onclick="updateOrderStatus(${order.id})">Mettre à jour</button>
                                <button class="btn btn-secondary" onclick="document.getElementById('orderModal').style.display='none';">Fermer</button>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('orderModalContent').innerHTML = modal;
                    document.getElementById('orderModal').style.display = 'flex';
                }
            })
            .catch(e => alert('Erreur lors du chargement des détails'));
    }
    
    function updateOrderStatus(orderId) {
        const status = document.getElementById('orderStatus').value;
        const fd = new FormData();
        fd.append('action', 'update_status');
        fd.append('id', orderId);
        fd.append('statut', status);
        
        fetch(ASSET_URL + 'handlers/crud_orders.php', {method: 'POST', body: fd})
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    document.getElementById('orderModal').style.display = 'none';
                    location.reload();
                }
            });
    }
    
    function deleteOrder(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette commande?')) {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            fetch(ASSET_URL + 'handlers/crud_orders.php', {method: 'POST', body: fd})
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                });
        }
    }
    
    // Fermer le modal quand on clique en dehors
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('orderModal');
        if (modal && e.target === modal) {
            modal.style.display = 'none';
        }
    });
</script>

<!-- Modal pour voir les détails de la commande -->
<div id="orderModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div id="orderModalContent" style="background:white; padding:2rem; border-radius:8px; max-width:700px; width:90%; max-height:90vh; overflow-y:auto;">
    </div>
</div>
