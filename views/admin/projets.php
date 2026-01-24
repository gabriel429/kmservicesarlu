<?php
/**
 * Page d'Administration des Projets
 */
?>

<div class="admin-section">
    <div class="section-header">
        <h2>Gestion des Projets</h2>
        <button class="btn btn-primary" onclick="openAddProjectModal()">+ Ajouter un Projet</button>
    </div>
    
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Localisation</th>
                    <th>Statut</th>
                    <th>Créé le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    if (!class_exists('MySQLCore')) {
                        require_once dirname(__DIR__, 2) . '/app/MySQL.php';
                    }
                    $projects = MySQLCore::fetchAll(
                        "SELECT id, titre, localisation, statut, created_at FROM projects ORDER BY created_at DESC"
                    );
                    
                    if (!empty($projects)):
                        foreach ($projects as $project):
                            $statusBadge = ($project['statut'] === 'realise') ? 'badge-success' : 'badge-info';
                            $statusText = ($project['statut'] === 'realise') ? 'Réalisé' : 'En cours';
                ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($project['titre']); ?></strong></td>
                                <td><?php echo htmlspecialchars($project['localisation'] ?? 'N/A'); ?></td>
                                <td><span class="badge <?php echo $statusBadge; ?>"><?php echo $statusText; ?></span></td>
                                <td><?php echo date('d/m/Y', strtotime($project['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="editProject(<?php echo $project['id']; ?>)">Éditer</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteProject(<?php echo $project['id']; ?>)">Supprimer</button>
                                </td>
                            </tr>
                <?php
                        endforeach;
                    else:
                ?>
                        <tr>
                            <td colspan="5" class="text-center">Aucun projet trouvé</td>
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
    
    .badge-info {
        background-color: #d1ecf1;
        color: #0c5460;
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
    function openAddProjectModal() {
        document.getElementById('projectForm').reset();
        document.getElementById('projectId').value = '';
        document.getElementById('modalTitle').textContent = 'Ajouter un Projet';
        document.getElementById('projectModal').style.display = 'flex';
    }
    
    function closeProjectModal() {
        document.getElementById('projectModal').style.display = 'none';
    }
    
    function editProject(id) {
        fetch('/kmservices/public/handlers/crud_projects.php?action=get&id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('projectId').value = data.data.id;
                    document.getElementById('projectTitre').value = data.data.titre;
                    document.getElementById('projectDescription').value = data.data.description;
                    document.getElementById('projectLocalisation').value = data.data.localisation;
                    document.getElementById('projectStatut').value = data.data.statut;
                    document.getElementById('projectActif').checked = data.data.actif;
                    document.getElementById('modalTitle').textContent = 'Éditer le Projet';
                    document.getElementById('projectModal').style.display = 'flex';
                }
            });
    }
    
    function deleteProject(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce projet ?')) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            
            fetch('/kmservices/public/handlers/crud_projects.php', {method: 'POST', body: formData})
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
    
    document.getElementById('projectForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('projectId').value;
        const action = id ? 'update' : 'create';
        
        const formData = new FormData();
        formData.append('action', action);
        formData.append('id', id);
        formData.append('titre', document.getElementById('projectTitre').value);
        formData.append('description', document.getElementById('projectDescription').value);
        formData.append('localisation', document.getElementById('projectLocalisation').value);
        formData.append('statut', document.getElementById('projectStatut').value);
        formData.append('actif', document.getElementById('projectActif').checked ? 1 : 0);
        
        // Ajouter les images
        const imageFiles = document.getElementById('projectImages').files;
        if (imageFiles.length > 5) {
            alert('Maximum 5 images autorisées');
            return;
        }
        for (let i = 0; i < imageFiles.length; i++) {
            formData.append('images[]', imageFiles[i]);
        }
        
        fetch('/kmservices/public/handlers/crud_projects.php', {method: 'POST', body: formData})
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeProjectModal();
                    location.reload();
                } else {
                    alert('Erreur: ' + data.message);
                }
            });
    });
    
    // Aperçu des images sélectionnées
    document.getElementById('projectImages')?.addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
        const files = Array.from(e.target.files);
        if (files.length > 5) {
            alert('Maximum 5 images autorisées');
            e.target.value = '';
            return;
        }
        
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = function(evt) {
                const img = document.createElement('img');
                img.src = evt.target.result;
                img.style.cssText = 'width: 100%; height: 80px; object-fit: cover; border-radius: 4px;';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });
    
    window.onclick = function(event) {
        const modal = document.getElementById('projectModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>

<!-- Modal de Formulaire de Projet -->
<div id="projectModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 600px; width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 id="modalTitle">Ajouter un Projet</h2>
            <button onclick="closeProjectModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        
        <form id="projectForm" enctype="multipart/form-data">
            <input type="hidden" id="projectId">
            
            <div style="margin-bottom: 1rem;">
                <label for="projectTitre" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Titre du Projet *</label>
                <input type="text" id="projectTitre" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label for="projectDescription" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Description</label>
                <textarea id="projectDescription" rows="4" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label for="projectLocalisation" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Localisation</label>
                    <input type="text" id="projectLocalisation" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label for="projectStatut" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Statut</label>
                    <select id="projectStatut" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="ongoing">En cours</option>
                        <option value="completed">Terminé</option>
                    </select>
                </div>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label for="projectImages" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Images du Projet (jusqu'à 5)</label>
                <input type="file" id="projectImages" multiple accept="image/*" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <small style="color: #666;">JPG, PNG, GIF (max 5MB par image)</small>
                <div id="imagePreview" style="margin-top: 1rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 8px;"></div>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" id="projectActif" style="margin-right: 0.5rem;">
                    <span>Actif</span>
                </label>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="closeProjectModal()" style="padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">Annuler</button>
                <button type="submit" style="padding: 8px 16px; background: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
