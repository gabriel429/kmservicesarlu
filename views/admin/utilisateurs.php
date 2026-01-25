<?php
/**
 * Page d'Administration des Utilisateurs
 */
?>

<div class="admin-section">
    <div class="section-header">
        <h2>Gestion des Utilisateurs</h2>
        <button class="btn btn-primary" onclick="openAddUserModal()">+ Ajouter un Utilisateur</button>
    </div>
    
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Date Création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    if (!class_exists('MySQLCore')) {
                        require_once dirname(__DIR__, 2) . '/app/MySQL.php';
                    }
                    $users = MySQLCore::fetchAll(
                        "SELECT id, nom, email, role, active, date_creation FROM users ORDER BY date_creation DESC"
                    );
                    
                    if (!empty($users)):
                        foreach ($users as $user):
                            $statusBadge = $user['active'] ? 'badge-success' : 'badge-danger';
                            $statusText = $user['active'] ? 'Actif' : 'Inactif';
                ?>
                            <tr>
                                <td data-label="Nom"><strong><?php echo htmlspecialchars($user['nom'] ?? ''); ?></strong></td>
                                <td data-label="Email"><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                                <td data-label="Rôle"><?php echo htmlspecialchars($user['role'] ?? ''); ?></td>
                                <td data-label="Statut"><span class="badge <?php echo $statusBadge; ?>"><?php echo $statusText; ?></span></td>
                                <td data-label="Date"><?php echo $user['date_creation'] ? date('d/m/Y', strtotime($user['date_creation'])) : 'N/A'; ?></td>
                                <td data-label="Actions">
                                    <button class="btn btn-sm btn-info" onclick="editUser(<?php echo $user['id']; ?>)">Éditer</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">Supprimer</button>
                                </td>
                            </tr>
                <?php
                        endforeach;
                    else:
                ?>
                        <tr>
                            <td colspan="6" class="text-center">Aucun utilisateur</td>
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
    function openAddUserModal() {
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        document.getElementById('userModalTitle').textContent = 'Ajouter un Utilisateur';
        document.getElementById('userModal').style.display = 'flex';
    }
    
    function closeUserModal() {
        document.getElementById('userModal').style.display = 'none';
    }
    
    function editUser(id) {
        fetch(ASSET_URL + 'handlers/crud_users.php?action=get&id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('userId').value = data.data.id;
                    document.getElementById('userNom').value = data.data.nom;
                    document.getElementById('userEmail').value = data.data.email;
                    document.getElementById('userRole').value = data.data.role;
                    document.getElementById('userActif').checked = data.data.active;
                    document.getElementById('userModalTitle').textContent = 'Éditer l\'Utilisateur';
                    document.getElementById('userModal').style.display = 'flex';
                }
            });
    }
    
    function deleteUser(id) {
        if (confirm('Êtes-vous sûr?')) {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            fetch(ASSET_URL + 'handlers/crud_users.php', {method: 'POST', body: fd})
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                });
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const userForm = document.getElementById('userForm');
        if (userForm) {
            userForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const fd = new FormData();
                fd.append('action', document.getElementById('userId').value ? 'update' : 'create');
                fd.append('id', document.getElementById('userId').value);
                fd.append('nom', document.getElementById('userNom').value);
                fd.append('email', document.getElementById('userEmail').value);
                fd.append('role', document.getElementById('userRole').value);
                fd.append('active', document.getElementById('userActif').checked ? 1 : 0);
                
                fetch(ASSET_URL + 'handlers/crud_users.php', {method: 'POST', body: fd})
                    .then(r => r.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            closeUserModal();
                            location.reload();
                        }
                    });
            });
        }
    });
    
    window.onclick = e => {
        if (e.target.id === 'userModal') closeUserModal();
    }
</script>

<!-- Modal de Formulaire Utilisateur -->
<div id="userModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 id="userModalTitle">Ajouter un Utilisateur</h2>
            <button onclick="closeUserModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        
        <form id="userForm">
            <input type="hidden" id="userId">
            
            <div style="margin-bottom: 1rem;">
                <label for="userNom" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Nom *</label>
                <input type="text" id="userNom" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label for="userEmail" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Email *</label>
                <input type="email" id="userEmail" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label for="userRole" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Rôle *</label>
                <select id="userRole" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
                    <option value="user">Utilisateur</option>
                    <option value="admin">Administrateur</option>
                    <option value="moderator">Modérateur</option>
                </select>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" id="userActif" style="margin-right: 0.5rem;">
                    <span>Actif</span>
                </label>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="closeUserModal()" style="padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">Annuler</button>
                <button type="submit" style="padding: 8px 16px; background: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
