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
                        "SELECT id, numero_commande, nom_client, montant_total, statut, created_at FROM orders ORDER BY created_at DESC LIMIT 100"
                    );
                    
                    if (!empty($orders)):
                        foreach ($orders as $order):
                            $statusBadge = $order['statut'] === 'livree' ? 'badge-success' : ($order['statut'] === 'nouvelle' ? 'badge-warning' : 'badge-info');
                ?>
                            <tr>
                                <td data-label="N°"><strong><?php echo htmlspecialchars($order['numero_commande'] ?? ''); ?></strong></td>
                                <td data-label="Client"><?php echo htmlspecialchars($order['nom_client'] ?? ''); ?></td>
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
                            <td colspan="6" class="text-center">Aucune commande</td>
                        </tr>
                <?php
                    endif;
                } catch (Exception $e) {
                    echo '<tr><td colspan="6" class="text-center alert alert-danger">Erreur: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function viewOrder(id) {
        alert('Détails de la commande #' + id);
    }
    
    function deleteOrder(id) {
        if (confirm('Êtes-vous sûr?')) {
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
</script>
