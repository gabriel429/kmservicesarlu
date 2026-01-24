<?php
/**
 * Page d'Administration des Commandes
 */
?>

<div class="admin-section">
    <h2>Commandes</h2>
    
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
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
                        "SELECT id, montant_total, statut, created_at FROM orders ORDER BY created_at DESC LIMIT 100"
                    );
                    
                    if (!empty($orders)):
                        foreach ($orders as $order):
                            $statusBadge = 'badge-info';
                            if ($order['statut'] === 'livree') $statusBadge = 'badge-success';
                            if ($order['statut'] === 'annulee') $statusBadge = 'badge-danger';
                            
                            $statusText = match($order['statut']) {
                                'nouvelle' => 'Nouvelle',
                                'confirmee' => 'Confirmée',
                                'preparee' => 'Préparée',
                                'livree' => 'Livrée',
                                'annulee' => 'Annulée',
                                default => ucfirst($order['statut'])
                            };
                ?>
                            <tr>
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($order['montant_total']); ?> USD</td>
                                <td><span class="badge <?php echo $statusBadge; ?>"><?php echo $statusText; ?></span></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewOrder(<?php echo $order['id']; ?>)">Voir</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteOrder(<?php echo $order['id']; ?>)">Supprimer</button>
                                </td>
                            </tr>
                <?php
                        endforeach;
                    else:
                ?>
                        <tr>
                            <td colspan="5" class="text-center">Aucune commande</td>
                        </tr>
                <?php
                    endif;
                } catch (Exception $e) {
                    echo '<tr><td colspan="6" class="text-center alert alert-danger">Erreur de chargement: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
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
    
    .badge-info {
        background-color: #d1ecf1;
        color: #0c5460;
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
    function viewOrder(id) {
        alert('Affichage de la commande ' + id + ' en développement');
    }
    
    function deleteOrder(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette commande ?')) {
            alert('Suppression de la commande ' + id + ' en développement');
        }
    }
</script>
