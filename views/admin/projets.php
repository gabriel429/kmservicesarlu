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
                    <th>Client</th>
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
                    $projects = MySQLCore::fetchAll(
                        "SELECT id, titre, client, statut, created_at FROM projects ORDER BY created_at DESC"
                    );
                    
                    if (!empty($projects)):
                        foreach ($projects as $project):
                            $statusBadge = $project['statut'] === 'completed' ? 'badge-success' : ($project['statut'] === 'in_progress' ? 'badge-warning' : 'badge-info');
                ?>
                            <tr>
                                <td data-label="Titre"><strong><?php echo htmlspecialchars($project['titre'] ?? ''); ?></strong></td>
                                <td data-label="Client"><?php echo htmlspecialchars($project['client'] ?? ''); ?></td>
                                <td data-label="Statut"><span class="badge <?php echo $statusBadge; ?>"><?php echo htmlspecialchars($project['statut'] ?? ''); ?></span></td>
                                <td data-label="Date"><?php echo $project['created_at'] ? date('d/m/Y', strtotime($project['created_at'])) : 'N/A'; ?></td>
                                <td data-label="Actions">
                                    <button class="btn btn-sm btn-info" onclick="editProject(<?php echo $project['id']; ?>)">Éditer</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteProject(<?php echo $project['id']; ?>)">Supprimer</button>
                                </td>
                            </tr>
                <?php
                        endforeach;
                    else:
                ?>
                        <tr>
                            <td colspan="5" class="text-center">Aucun projet</td>
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
    function openAddProjectModal() {
        document.getElementById('projectForm').reset();
        document.getElementById('projectId').value = '';
        document.getElementById('projectModalTitle').textContent = 'Ajouter un Projet';
        document.getElementById('projectModal').style.display = 'flex';
    }
    
    function closeProjectModal() {
        document.getElementById('projectModal').style.display = 'none';
    }
    
    function editProject(id) {
        fetch(ASSET_URL + 'handlers/crud_projects.php?action=get&id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('projectId').value = data.data.id;
                    document.getElementById('projectTitre').value = data.data.titre;
                    document.getElementById('projectClient').value = data.data.client;
                    document.getElementById('projectStatut').value = data.data.statut;
                    document.getElementById('projectModalTitle').textContent = 'Éditer le Projet';
                    document.getElementById('projectModal').style.display = 'flex';
                }
            });
    }
    
    function deleteProject(id) {
        if (confirm('Êtes-vous sûr?')) {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            fetch(ASSET_URL + 'handlers/crud_projects.php', {method: 'POST', body: fd})
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                });
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const projectForm = document.getElementById('projectForm');
        if (projectForm) {
            projectForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const fd = new FormData();
                fd.append('action', document.getElementById('projectId').value ? 'update' : 'create');
                fd.append('id', document.getElementById('projectId').value);
                fd.append('titre', document.getElementById('projectTitre').value);
                fd.append('client', document.getElementById('projectClient').value);
                fd.append('statut', document.getElementById('projectStatut').value);
                
                // Ajouter les fichiers images
                const imageInput = document.getElementById('projectImages');
                if (imageInput && imageInput.files) {
                    for (let i = 0; i < imageInput.files.length; i++) {
                        fd.append('images[]', imageInput.files[i]);
                    }
                }
                
                fetch(ASSET_URL + 'handlers/crud_projects.php', {method: 'POST', body: fd})
                    .then(r => r.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            closeProjectModal();
                            location.reload();
                        }
                    });
            });
        }
    });
    
    window.onclick = e => {
        if (e.target.id === 'projectModal') closeProjectModal();
    }
</script>

<!-- Modal de Formulaire Projet -->
<div id="projectModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 id="projectModalTitle">Ajouter un Projet</h2>
            <button onclick="closeProjectModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        
        <form id="projectForm" enctype="multipart/form-data">
            <input type="hidden" id="projectId">
            
            <div style="margin-bottom: 1rem;">
                <label for="projectTitre" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Titre *</label>
                <input type="text" id="projectTitre" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label for="projectClient" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Client *</label>
                <input type="text" id="projectClient" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label for="projectStatut" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Statut *</label>
                <select id="projectStatut" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
                    <option value="planned">Planifié</option>
                    <option value="in_progress">En cours</option>
                    <option value="completed">Complété</option>
                    <option value="archived">Archivé</option>
                </select>
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="projectImages" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Images du Projet (Jusqu'à 5)</label>
                <input type="file" id="projectImages" name="images" multiple accept="image/*" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
                <small style="color: #666;">JPG, PNG, GIF (max 5 images, 5MB chacune)</small>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="closeProjectModal()" style="padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">Annuler</button>
                <button type="submit" style="padding: 8px 16px; background: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
