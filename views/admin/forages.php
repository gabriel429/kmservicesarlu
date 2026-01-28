<?php
/**
 * Page d'Administration des Forages
 */
?>

<div class="admin-section">
    <div class="section-header">
        <h2>Gestion des Forages</h2>
    </div>
    
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Localisation</th>
                    <th>Profondeur</th>
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
                    $forages = MySQLCore::fetchAll(
                        "SELECT id, localisation, profondeur_estimee, statut, created_at FROM drilling_requests ORDER BY created_at DESC"
                    );
                    
                    if (!empty($forages)):
                        foreach ($forages as $forage):
                            $statusBadge = $forage['statut'] === 'complete' ? 'badge-success' : 'badge-warning';
                ?>
                            <tr>
                                <td data-label="Localisation"><strong><?php echo htmlspecialchars($forage['localisation'] ?? ''); ?></strong></td>
                                <td data-label="Profondeur"><?php echo htmlspecialchars($forage['profondeur_estimee'] ?? ''); ?> m</td>
                                <td data-label="Statut"><span class="badge <?php echo $statusBadge; ?>"><?php echo htmlspecialchars($forage['statut'] ?? ''); ?></span></td>
                                <td data-label="Date"><?php echo $forage['created_at'] ? date('d/m/Y', strtotime($forage['created_at'])) : 'N/A'; ?></td>
                                <td data-label="Actions">
                                    <button class="btn btn-sm btn-info" onclick="editForage(<?php echo $forage['id']; ?>)">Éditer</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteForage(<?php echo $forage['id']; ?>)">Supprimer</button>
                                </td>
                            </tr>
                <?php
                        endforeach;
                    else:
                ?>
                        <tr>
                            <td colspan="5" class="text-center">Aucun forage</td>
                        </tr>
                <?php
                    endif;
                } catch (Exception $e) {
                    echo '<tr><td colspan="5" class="text-center alert alert-danger">Erreur: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function openAddForageModal() {
        document.getElementById('forageForm').reset();
        document.getElementById('forageId').value = '';
        document.getElementById('forageModalTitle').textContent = 'Ajouter un Forage';
        document.getElementById('forageModal').style.display = 'flex';
    }
    
    function closeForageModal() {
        document.getElementById('forageModal').style.display = 'none';
    }
    
    function editForage(id) {
        fetch(ASSET_URL + 'handlers/crud_forages.php?action=get&id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('forageId').value = data.data.id;
                    document.getElementById('forageLocalisation').value = data.data.localisation;
                    document.getElementById('forageProfondeur').value = data.data.profondeur_estimee;
                    document.getElementById('forageStatut').value = data.data.statut;
                    document.getElementById('forageModalTitle').textContent = 'Éditer le Forage';
                    document.getElementById('forageModal').style.display = 'flex';
                }
            });
    }
    
    function deleteForage(id) {
        if (confirm('Êtes-vous sûr?')) {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            fetch(ASSET_URL + 'handlers/crud_forages.php', {method: 'POST', body: fd})
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                });
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const forageForm = document.getElementById('forageForm');
        if (forageForm) {
            forageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const fd = new FormData();
                fd.append('action', document.getElementById('forageId').value ? 'update' : 'create');
                fd.append('id', document.getElementById('forageId').value);
                fd.append('localisation', document.getElementById('forageLocalisation').value);
                fd.append('profondeur_estimee', document.getElementById('forageProfondeur').value);
                fd.append('statut', document.getElementById('forageStatut').value);
                
                fetch(ASSET_URL + 'handlers/crud_forages.php', {method: 'POST', body: fd})
                    .then(r => r.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            closeForageModal();
                            location.reload();
                        }
                    });
            });
        }
    });
    
    window.onclick = e => {
        if (e.target.id === 'forageModal') closeForageModal();
    }
</script>

<!-- Modal de Formulaire Forage -->
<div id="forageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 id="forageModalTitle">Ajouter un Forage</h2>
            <button onclick="closeForageModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        
        <form id="forageForm">
            <input type="hidden" id="forageId">
            
            <div style="margin-bottom: 1rem;">
                <label for="forageLocalisation" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Localisation *</label>
                <input type="text" id="forageLocalisation" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label for="forageProfondeur" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Profondeur (m) *</label>
                <input type="number" id="forageProfondeur" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label for="forageStatut" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Statut *</label>
                <select id="forageStatut" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
                    <option value="nouveau">Nouveau</option>
                    <option value="en_attente">En attente</option>
                    <option value="contacte">Contacté</option>
                    <option value="complete">Complété</option>
                    <option value="refuse">Refusé</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="closeForageModal()" style="padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">Annuler</button>
                <button type="submit" style="padding: 8px 16px; background: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
