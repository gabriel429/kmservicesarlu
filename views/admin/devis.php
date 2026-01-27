<?php
/**
 * Page d'Administration des Demandes de Devis
 */
?>

<div class="admin-section">
    <h2>Gestion des Demandes de Devis</h2>
    
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>N° Devis</th>
                    <th>Client</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Service</th>
                    <th>Budget</th>
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
                    
                    // Créer la table si elle n'existe pas
                    try {
                        MySQLCore::execute(
                            "CREATE TABLE IF NOT EXISTS quote_requests (
                                id INT PRIMARY KEY AUTO_INCREMENT,
                                numero_devis VARCHAR(50) UNIQUE NOT NULL,
                                nom VARCHAR(150) NOT NULL,
                                email VARCHAR(150) NOT NULL,
                                telephone VARCHAR(20) NOT NULL,
                                localisation VARCHAR(255),
                                service VARCHAR(100),
                                type_service VARCHAR(100),
                                description LONGTEXT,
                                delai_souhaite VARCHAR(100),
                                budget_estime DECIMAL(12, 2),
                                document_joint VARCHAR(255),
                                statut ENUM('nouveau', 'en_attente', 'contacte', 'accepte', 'refuse') DEFAULT 'nouveau',
                                lu TINYINT DEFAULT 0,
                                notes TEXT,
                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                treated_by INT DEFAULT NULL,
                                INDEX idx_statut (statut),
                                INDEX idx_created_at (created_at),
                                FOREIGN KEY (treated_by) REFERENCES users(id) ON DELETE SET NULL
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
                        );
                    } catch (Throwable $te) {
                        // Table exists
                    }
                    
                    $quotes = MySQLCore::fetchAll(
                        "SELECT id, numero_devis, nom, email, telephone, service, budget_estime, statut, created_at FROM quote_requests ORDER BY created_at DESC LIMIT 100"
                    );
                    
                    if (!empty($quotes)):
                        foreach ($quotes as $quote):
                            $statusBadge = $quote['statut'] === 'accepte' ? 'badge-success' : ($quote['statut'] === 'nouveau' ? 'badge-warning' : ($quote['statut'] === 'refuse' ? 'badge-danger' : 'badge-info'));
                ?>
                            <tr>
                                <td data-label="N°"><strong><?php echo htmlspecialchars($quote['numero_devis'] ?? ''); ?></strong></td>
                                <td data-label="Client"><?php echo htmlspecialchars($quote['nom'] ?? ''); ?></td>
                                <td data-label="Email"><a href="mailto:<?php echo htmlspecialchars($quote['email'] ?? ''); ?>"><?php echo htmlspecialchars($quote['email'] ?? ''); ?></a></td>
                                <td data-label="Téléphone"><a href="tel:<?php echo htmlspecialchars($quote['telephone'] ?? ''); ?>"><?php echo htmlspecialchars($quote['telephone'] ?? 'N/A'); ?></a></td>
                                <td data-label="Service"><?php echo htmlspecialchars(ucfirst($quote['service'] ?? '')); ?></td>
                                <td data-label="Budget"><?php echo $quote['budget_estime'] ? '$' . number_format((float)$quote['budget_estime'], 2) : 'N/A'; ?></td>
                                <td data-label="Statut"><span class="badge <?php echo $statusBadge; ?>"><?php echo htmlspecialchars($quote['statut'] ?? ''); ?></span></td>
                                <td data-label="Date"><?php echo $quote['created_at'] ? date('d/m/Y H:i', strtotime($quote['created_at'])) : 'N/A'; ?></td>
                                <td data-label="Actions">
                                    <button class="btn btn-sm btn-info" onclick="viewQuote(<?php echo $quote['id']; ?>)">Voir</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteQuote(<?php echo $quote['id']; ?>)">Supprimer</button>
                                </td>
                            </tr>
                <?php
                        endforeach;
                    else:
                ?>
                        <tr>
                            <td colspan="9" class="text-center">Aucune demande de devis</td>
                        </tr>
                <?php
                    endif;
                } catch (Exception $e) {
                    echo '<tr><td colspan="9" class="text-center alert alert-danger">Erreur: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal pour voir les détails du devis -->
<div id="quoteModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div id="quoteModalContent" style="background:white; padding:2rem; border-radius:8px; max-width:700px; width:90%; max-height:90vh; overflow-y:auto;">
    </div>
</div>

<script>
    function viewQuote(id) {
        fetch(ASSET_URL + 'handlers/crud_devis.php?action=get&id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const quote = data.data;
                    
                    const modal = `
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                            <h2>Devis ${quote.numero_devis}</h2>
                            <button onclick="document.getElementById('quoteModal').style.display='none';" style="background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>
                        </div>
                        
                        <div style="background:#f9f9f9; padding:1rem; border-radius:4px; margin-bottom:1.5rem;">
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                                <div>
                                    <p><strong>Client:</strong> ${quote.nom}</p>
                                    <p><strong>Email:</strong> <a href="mailto:${quote.email}">${quote.email}</a></p>
                                    <p><strong>Téléphone:</strong> <a href="tel:${quote.telephone}">${quote.telephone}</a></p>
                                </div>
                                <div>
                                    <p><strong>Date:</strong> ${new Date(quote.created_at).toLocaleString('fr-FR')}</p>
                                    <p><strong>Service:</strong> ${quote.service}</p>
                                    ${quote.budget_estime ? `<p><strong>Budget:</strong> $${parseFloat(quote.budget_estime).toFixed(2)}</p>` : ''}
                                </div>
                            </div>
                        </div>
                        
                        ${quote.localisation ? `<p><strong>Localisation:</strong> ${quote.localisation}</p>` : ''}
                        ${quote.type_service ? `<p><strong>Type:</strong> ${quote.type_service}</p>` : ''}
                        ${quote.delai_souhaite ? `<p><strong>Délai souhaité:</strong> ${quote.delai_souhaite}</p>` : ''}
                        
                        ${quote.description ? `
                            <h3>Description</h3>
                            <p style="background:#f5f5f5; padding:1rem; border-radius:4px; white-space:pre-wrap;">${quote.description}</p>
                        ` : ''}
                        
                        <h3>Gestion</h3>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.5rem;">
                            <div>
                                <label><strong>Statut</strong></label>
                                <select id="quoteStatus" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                                    <option value="nouveau" ${quote.statut === 'nouveau' ? 'selected' : ''}>Nouveau</option>
                                    <option value="en_attente" ${quote.statut === 'en_attente' ? 'selected' : ''}>En attente</option>
                                    <option value="contacte" ${quote.statut === 'contacte' ? 'selected' : ''}>Contacté</option>
                                    <option value="accepte" ${quote.statut === 'accepte' ? 'selected' : ''}>Accepté</option>
                                    <option value="refuse" ${quote.statut === 'refuse' ? 'selected' : ''}>Refusé</option>
                                </select>
                            </div>
                            <div>
                                <label><strong>Notes</strong></label>
                                <input type="text" id="quoteNotes" value="${quote.notes || ''}" placeholder="Notes internes" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            </div>
                        </div>
                        
                        <div style="display:flex; gap:1rem; margin-top:1.5rem;">
                            <button class="btn btn-primary" onclick="updateQuoteStatus(${quote.id})">Mettre à jour</button>
                            <button class="btn btn-secondary" onclick="document.getElementById('quoteModal').style.display='none';">Fermer</button>
                        </div>
                    `;
                    
                    document.getElementById('quoteModalContent').innerHTML = modal;
                    document.getElementById('quoteModal').style.display = 'flex';
                }
            })
            .catch(e => alert('Erreur lors du chargement des détails'));
    }
    
    function updateQuoteStatus(quoteId) {
        const status = document.getElementById('quoteStatus').value;
        const notes = document.getElementById('quoteNotes').value;
        const fd = new FormData();
        fd.append('action', 'update_status');
        fd.append('id', quoteId);
        fd.append('statut', status);
        fd.append('notes', notes);
        
        fetch(ASSET_URL + 'handlers/crud_devis.php', {method: 'POST', body: fd})
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    document.getElementById('quoteModal').style.display = 'none';
                    location.reload();
                }
            });
    }
    
    function deleteQuote(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette demande?')) {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            fetch(ASSET_URL + 'handlers/crud_devis.php', {method: 'POST', body: fd})
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                });
        }
    }
    
    // Fermer le modal quand on clique en dehors
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('quoteModal');
        if (modal && e.target === modal) {
            modal.style.display = 'none';
        }
    });
</script>
