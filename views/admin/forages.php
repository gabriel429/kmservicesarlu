<?php
/**
 * Page d'Administration des Demandes de Forage
 */
?>

<div class="admin-section">
    <h2>Demandes de Forage</h2>
    
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Type de Forage</th>
                    <th>Localisation</th>
                    <th>Doc</th>
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
                    $forages = MySQLCore::fetchAll(
                        "SELECT id, nom, email, type_forage, localisation, statut, document_joint, created_at FROM drilling_requests ORDER BY created_at DESC LIMIT 100"
                    );
                    
                    if (!empty($forages)):
                        foreach ($forages as $forage):
                            $statusBadge = 'badge-warning';
                            if ($forage['statut'] === 'complete') $statusBadge = 'badge-success';
                            if ($forage['statut'] === 'refuse') $statusBadge = 'badge-danger';
                            
                            $statusText = match($forage['statut']) {
                                'nouveau' => 'Nouveau',
                                'en_attente' => 'En attente',
                                'contacte' => 'Contacté',
                                'complete' => 'Complété',
                                'refuse' => 'Refusé',
                                default => ucfirst($forage['statut'])
                            };
                ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($forage['nom']); ?></strong></td>
                                <td><?php echo htmlspecialchars($forage['email']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $forage['type_forage']))); ?></td>
                                <td><?php echo htmlspecialchars($forage['localisation']); ?></td>
                                <td>
                                    <?php if (!empty($forage['document_joint'])): ?>
                                        <a href="<?php echo APP_URL . ltrim($forage['document_joint'], '/'); ?>" target="_blank" rel="noopener noreferrer">Télécharger</a>
                                    <?php else: ?>
                                        <span style="color:#999;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?php echo $statusBadge; ?>"><?php echo $statusText; ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewForage(<?php echo $forage['id']; ?>)">Voir</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteForage(<?php echo $forage['id']; ?>)">Supprimer</button>
                                </td>
                            </tr>
                <?php
                        endforeach;
                    else:
                ?>
                        <tr>
                            <td colspan="6" class="text-center">Aucune demande de forage</td>
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
    
    .badge-warning {
        background-color: #fff3cd;
        color: #856404;
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
    function viewForage(id) {
        fetch('/kmservices/public/handlers/crud_forages.php?action=get&id=' + id)
            .then(r => {
                if (!r.ok) throw new Error('Erreur HTTP: ' + r.status);
                return r.json();
            })
            .then(data => {
                if (data.success && data.data) {
                    const it = data.data;
                    document.getElementById('forageId').value = it.id;
                    document.getElementById('fNom').textContent = it.nom || '';
                    document.getElementById('fEmail').textContent = it.email || '';
                    document.getElementById('fTel').textContent = it.telephone || '';
                    document.getElementById('fType').textContent = (it.type_forage || '').replace(/_/g,' ');
                    document.getElementById('fLoc').textContent = it.localisation || '';
                    document.getElementById('fDelai').textContent = it.delai_souhaite || '';
                    document.getElementById('fBudget').textContent = it.budget_estime != null ? String(it.budget_estime) : '';
                    document.getElementById('fDate').textContent = it.created_at ? new Date(it.created_at).toLocaleString('fr-FR') : '';
                    document.getElementById('fDesc').textContent = it.description || '';

                    const docWrap = document.getElementById('fDocWrap');
                    const docLink = document.getElementById('fDocLink');
                    const docPreview = document.getElementById('fDocPreview');
                    docWrap.style.display = 'none';
                    docLink.removeAttribute('href');
                    docPreview.innerHTML = '';
                    if (it.document_joint) {
                        const rel = String(it.document_joint).replace(/^\/+/, '');
                        const url = (window.APP_URL || (window.location.origin + '/kmservices/public/').replace(/public\/$/, '')) + rel;
                        // Utiliser APP_URL du backend si disponible via data-attr
                        const appUrl = document.body.getAttribute('data-app-url');
                        const finalUrl = appUrl ? (appUrl + rel) : url;
                        docWrap.style.display = 'block';
                        docLink.href = finalUrl;
                        docLink.textContent = 'Télécharger le document';
                        const lower = rel.toLowerCase();
                        const isImg = lower.endsWith('.jpg') || lower.endsWith('.jpeg') || lower.endsWith('.png') || lower.endsWith('.webp');
                        if (isImg) {
                            const img = document.createElement('img');
                            img.src = finalUrl;
                            img.alt = 'Aperçu du document';
                            img.style.maxWidth = '100%';
                            img.style.maxHeight = '260px';
                            img.style.border = '1px solid #eee';
                            img.style.borderRadius = '4px';
                            docPreview.appendChild(img);
                        } else {
                            const note = document.createElement('div');
                            note.textContent = 'Aperçu non disponible. Utilisez le lien pour ouvrir le document.';
                            note.style.color = '#666';
                            docPreview.appendChild(note);
                        }
                    }
                    document.getElementById('forageModal').style.display = 'flex';
                } else {
                    alert('Erreur: ' + (data.message || 'Impossible de charger la demande'));
                }
            })
            .catch(err => {
                console.error('Erreur:', err);
                alert('Erreur lors du chargement: ' + err.message);
            });
    }
    
    function deleteForage(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette demande ?')) {
            alert('Suppression de la demande ' + id + ' en développement');
        }
    }
</script>

<!-- Modal Détails Forage -->
<div id="forageModal" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background:white; padding:2rem; border-radius:8px; max-width: 760px; width: 92%; max-height: 85vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3>Demande de Forage</h3>
            <button onclick="document.getElementById('forageModal').style.display='none'" style="background:none; border:none; font-size:1.5rem; cursor:pointer;">×</button>
        </div>
        <input type="hidden" id="forageId">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div><strong>Nom:</strong><div id="fNom" style="color:#333; margin-top:4px;"></div></div>
            <div><strong>Email:</strong><div id="fEmail" style="color:#333; margin-top:4px;"></div></div>
            <div><strong>Téléphone:</strong><div id="fTel" style="color:#333; margin-top:4px;"></div></div>
            <div><strong>Type de forage:</strong><div id="fType" style="color:#333; margin-top:4px;"></div></div>
            <div><strong>Localisation:</strong><div id="fLoc" style="color:#333; margin-top:4px;"></div></div>
            <div><strong>Délai souhaité:</strong><div id="fDelai" style="color:#333; margin-top:4px;"></div></div>
            <div><strong>Budget estimé:</strong><div id="fBudget" style="color:#333; margin-top:4px;"></div></div>
            <div><strong>Date:</strong><div id="fDate" style="color:#999; margin-top:4px;"></div></div>
        </div>
        <div style="margin-top:1rem;">
            <strong>Description:</strong>
            <div id="fDesc" style="margin-top:6px; padding:1rem; background:#f9f9f9; border-left:3px solid #17a2b8; border-radius:4px; white-space:pre-wrap;"></div>
        </div>
        <div id="fDocWrap" style="margin-top:1rem; display:none;">
            <strong>Document joint:</strong>
            <div id="fDocPreview" style="margin:8px 0;"></div>
            <a id="fDocLink" href="#" target="_blank" rel="noopener noreferrer" style="display:inline-block; padding:8px 12px; background:#007bff; color:white; border-radius:4px; text-decoration:none;">Télécharger le document</a>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:1.25rem;">
            <button onclick="document.getElementById('forageModal').style.display='none'" style="padding:8px 16px; border:1px solid #ddd; background:white; border-radius:4px; cursor:pointer;">Fermer</button>
        </div>
    </div>
    <script>
        // Expose APP_URL pour résolution d'URL si présent côté backend
        document.body.setAttribute('data-app-url', (typeof APP_URL !== 'undefined') ? APP_URL : '');
    </script>
</div>
