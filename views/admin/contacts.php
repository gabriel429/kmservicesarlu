<?php
/**
 * Page d'Administration des Messages de Contact
 */
?>

<div class="admin-section">
    <h2>Messages de Contact</h2>
    
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Sujet</th>
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
                    $contacts = MySQLCore::fetchAll(
                        "SELECT id, nom, email, sujet, statut, created_at FROM contacts ORDER BY created_at DESC LIMIT 100"
                    );
                    
                    if (!empty($contacts)):
                        foreach ($contacts as $contact):
                            $statusBadge = $contact['statut'] === 'traite' ? 'badge-secondary' : 'badge-warning';
                            $statusText = $contact['statut'] === 'traite' ? 'Traité' : 'Nouveau';
                            if ($contact['statut'] === 'archive') {
                                $statusBadge = 'badge-info';
                                $statusText = 'Archivé';
                            }
                ?>
                            <tr>
                                <td data-label="Nom"><strong><?php echo htmlspecialchars($contact['nom'] ?? ''); ?></strong></td>
                                <td data-label="Email"><?php echo htmlspecialchars($contact['email'] ?? ''); ?></td>
                                <td data-label="Sujet"><?php echo htmlspecialchars(substr($contact['sujet'] ?? '', 0, 40)); ?></td>
                                <td data-label="Statut"><span class="badge <?php echo $statusBadge; ?>"><?php echo $statusText; ?></span></td>
                                <td data-label="Date"><?php echo $contact['created_at'] ? date('d/m/Y H:i', strtotime($contact['created_at'])) : 'N/A'; ?></td>
                                <td data-label="Actions">
                                    <button class="btn btn-sm btn-info" onclick="viewContact(<?php echo $contact['id']; ?>)">Voir</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteContact(<?php echo $contact['id']; ?>)">Supprimer</button>
                                </td>
                            </tr>
                <?php
                        endforeach;
                    else:
                ?>
                        <tr>
                            <td colspan="6" data-label="Message" class="text-center">Aucun message</td>
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
    function viewContact(id) {
        fetch(ASSET_URL + 'handlers/crud_contacts.php?action=get&id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('contactId').value = data.data.id;
                    document.getElementById('contactNom').textContent = data.data.nom;
                    document.getElementById('contactEmail').textContent = data.data.email;
                    document.getElementById('contactTelephone').textContent = data.data.telephone || 'N/A';
                    document.getElementById('contactSujet').textContent = data.data.sujet;
                    document.getElementById('contactDate').textContent = new Date(data.data.created_at).toLocaleString('fr-FR');
                    document.getElementById('contactMessage').innerHTML = escapeHtml(data.data.message).replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank">$1</a>');
                    document.getElementById('contactModal').style.display = 'flex';
                    document.getElementById('replyForm').style.display = 'none';
                }
            });
    }
    
    function closeContactModal() {
        document.getElementById('contactModal').style.display = 'none';
    }
    
    function deleteContact(id) {
        if (confirm('Êtes-vous sûr?')) {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            fetch(ASSET_URL + 'handlers/crud_contacts.php', {method: 'POST', body: fd})
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                });
        }
    }
    
    function toggleReplyForm() {
        document.getElementById('replyForm').style.display = 
            document.getElementById('replyForm').style.display === 'none' ? 'block' : 'none';
    }
    
    function markAsProcessed() {
        const fd = new FormData();
        fd.append('action', 'update_status');
        fd.append('id', document.getElementById('contactId').value);
        fd.append('statut', 'traite');
        fetch(ASSET_URL + 'handlers/crud_contacts.php', {method: 'POST', body: fd})
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
            });
    }
    
    function sendReply() {
        const fd = new FormData();
        fd.append('action', 'reply');
        fd.append('id', document.getElementById('contactId').value);
        fd.append('message', document.getElementById('replyMessage').value);
        fetch(ASSET_URL + 'handlers/crud_contacts.php', {method: 'POST', body: fd})
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    closeContactModal();
                    location.reload();
                }
            });
    }
    
    function escapeHtml(text) {
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
    window.onclick = e => {
        if (e.target.id === 'contactModal') closeContactModal();
    }
</script>

<!-- Modal de Détails du Contact -->
<div id="contactModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Détails du Message</h2>
            <button onclick="closeContactModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        
        <input type="hidden" id="contactId">
        
        <div style="margin-bottom: 1.5rem; padding: 1rem; background: #f9f9f9; border-radius: 4px;">
            <div style="margin-bottom: 1rem;">
                <strong>Nom:</strong>
                <p id="contactNom" style="margin: 0.5rem 0; color: #333;"></p>
            </div>
            <div style="margin-bottom: 1rem;">
                <strong>Email:</strong>
                <p id="contactEmail" style="margin: 0.5rem 0; color: #333;"></p>
            </div>
            <div style="margin-bottom: 1rem;">
                <strong>Téléphone:</strong>
                <p id="contactTelephone" style="margin: 0.5rem 0; color: #333;"></p>
            </div>
            <div style="margin-bottom: 1rem;">
                <strong>Sujet:</strong>
                <p id="contactSujet" style="margin: 0.5rem 0; color: #333;"></p>
            </div>
            <div>
                <strong>Date:</strong>
                <p id="contactDate" style="margin: 0.5rem 0; color: #999; font-size: 0.9rem;"></p>
            </div>
        </div>
        
        <div style="margin-bottom: 1.5rem;">
            <strong>Message:</strong>
            <div id="contactMessage" style="margin-top: 0.5rem; padding: 1rem; background: #f9f9f9; border-left: 3px solid #17a2b8; border-radius: 4px; word-wrap: break-word;"></div>
        </div>
        
        <div style="display: flex; gap: 1rem; justify-content: flex-end; flex-wrap: wrap;">
            <button onclick="closeContactModal()" style="padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer; font-size: 16px;">Fermer</button>
            <button onclick="toggleReplyForm()" style="padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">Répondre</button>
            <button onclick="markAsProcessed()" style="padding: 8px 16px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">Traité</button>
            <button onclick="deleteContact(document.getElementById('contactId').value); closeContactModal();" style="padding: 8px 16px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">Supprimer</button>
        </div>
        
        <div id="replyForm" style="display: none; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e0e0e0;">
            <h3>Envoyer une Réponse</h3>
            <div style="margin-bottom: 1rem;">
                <label for="replyMessage" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Message *</label>
                <textarea id="replyMessage" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;" rows="6" placeholder="Écrivez votre réponse..."></textarea>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button onclick="sendReply()" style="padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Envoyer</button>
                <button onclick="toggleReplyForm()" style="padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">Annuler</button>
            </div>
        </div>
    </div>
</div>
