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
                    <th>Nom d'utilisateur</th>
                    <th>Email</th>
                    <th>Rôle</th>
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
                    $users = MySQLCore::fetchAll(
                        "SELECT id, username, email, role FROM users ORDER BY id DESC"
                    );
                    
                    if (!empty($users)):
                        foreach ($users as $user):
                            $statusBadge = 'badge-success';
                            $roleText = ucfirst($user['role']);
                ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $roleText; ?></td>
                                <td><span class="badge <?php echo $statusBadge; ?>">Actif</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="editUser(<?php echo $user['id']; ?>)">Éditer</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">Supprimer</button>
                                </td>
                            </tr>
                <?php
                        endforeach;
                    else:
                ?>
                        <tr>
                            <td colspan="5" class="text-center">Aucun utilisateur trouvé</td>
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
    function openAddUserModal() {
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        document.getElementById('userPassword').required = true;
        document.getElementById('modalTitle').textContent = 'Ajouter un Utilisateur';
        document.getElementById('userModal').style.display = 'flex';
    }
    
    function closeUserModal() {
        document.getElementById('userModal').style.display = 'none';
    }
    
    function editUser(id) {
        fetch('/handlers/crud_users.php?action=get&id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data) {
                    document.getElementById('userId').value = data.data.id;
                    document.getElementById('userUsername').value = data.data.username;
                    document.getElementById('userEmail').value = data.data.email;
                    document.getElementById('userRole').value = data.data.role;
                    document.getElementById('userPassword').value = '';
                    document.getElementById('userPassword').required = false;
                    document.getElementById('userPassword').placeholder = 'Laisser vide pour ne pas changer';
                    document.getElementById('modalTitle').textContent = 'Éditer l\'Utilisateur';
                    document.getElementById('userModal').style.display = 'flex';
                } else {
                    alert('Erreur: ' + (data.message || 'Impossible de charger l\'utilisateur'));
                }
            })
            .catch(err => console.error('Erreur:', err));
    }
    
    function deleteUser(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            
            fetch('/handlers/crud_users.php', {method: 'POST', body: formData})
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                })
                .catch(err => console.error('Erreur:', err));
        }
    }
    
    document.getElementById('userForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('userId').value;
        const action = id ? 'update' : 'create';
        
        const formData = new FormData();
        formData.append('action', action);
        formData.append('id', id);
        formData.append('username', document.getElementById('userUsername').value);
        formData.append('email', document.getElementById('userEmail').value);
        formData.append('role', document.getElementById('userRole').value);
        if (document.getElementById('userPassword').value) {
            formData.append('password', document.getElementById('userPassword').value);
        }
        
        fetch('/handlers/crud_users.php', {method: 'POST', body: formData})
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeUserModal();
                    location.reload();
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(err => console.error('Erreur:', err));
    });
    
    window.onclick = function(event) {
        const modal = document.getElementById('userModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>

<!-- Modal de Formulaire d'Utilisateur -->
<div id="userModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 id="modalTitle">Ajouter un Utilisateur</h2>
            <button onclick="closeUserModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        
        <form id="userForm">
            <input type="hidden" id="userId">
            
            <div style="margin-bottom: 1rem;">
                <label for="userUsername" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Nom d'utilisateur *</label>
                <input type="text" id="userUsername" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label for="userEmail" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Email *</label>
                <input type="email" id="userEmail" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label for="userPassword" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Mot de passe *</label>
                <input type="password" id="userPassword" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label for="userRole" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Rôle</label>
                <select id="userRole" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="editor">Éditeur</option>
                    <option value="admin">Administrateur</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="closeUserModal()" style="padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">Annuler</button>
                <button type="submit" style="padding: 8px 16px; background: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
