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
                                <td><strong><?php echo htmlspecialchars($contact['nom']); ?></strong></td>
                                <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                <td><?php echo htmlspecialchars(substr($contact['sujet'], 0, 40)); ?></td>
                                <td><span class="badge <?php echo $statusBadge; ?>"><?php echo $statusText; ?></span></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewContact(<?php echo $contact['id']; ?>)">Voir</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteContact(<?php echo $contact['id']; ?>)">Supprimer</button>
                                </td>
                            </tr>
                <?php
                        endforeach;
                    else:
                ?>
                        <tr>
                            <td colspan="6" class="text-center">Aucun message</td>
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
    
    .badge-warning {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .badge-secondary {
        background-color: #e2e3e5;
        color: #383d41;
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
    function viewContact(id) {
        fetch('/kmservices/public/handlers/crud_contacts.php?action=get&id=' + id)
            .then(r => {
                if (!r.ok) throw new Error('Erreur HTTP: ' + r.status);
                return r.json();
            })
            .then(data => {
                if (data.success && data.data) {
                    const contact = data.data;
                    document.getElementById('contactId').value = contact.id;
                    document.getElementById('contactNom').textContent = contact.nom;
                    document.getElementById('contactEmail').textContent = contact.email;
                    document.getElementById('contactTelephone').textContent = contact.telephone || 'Non fourni';
                    document.getElementById('contactSujet').textContent = contact.sujet;
                    const container = document.getElementById('contactMessage');
                    container.innerHTML = linkifyAndEscape(contact.message || '');
                    document.getElementById('contactDate').textContent = new Date(contact.created_at).toLocaleString('fr-FR');
                    document.getElementById('contactModal').style.display = 'flex';
                } else {
                    alert('Erreur: ' + (data.message || 'Impossible de charger le message'));
                }
            })
            .catch(err => {
                console.error('Erreur:', err);
                alert('Erreur lors du chargement du message: ' + err.message);
            });
    }
    
    function closeContactModal() {
        document.getElementById('contactModal').style.display = 'none';
    }
    
    function deleteContact(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            
            fetch('/kmservices/public/handlers/crud_contacts.php', {method: 'POST', body: formData})
                .then(r => {
                    if (!r.ok) throw new Error('Erreur HTTP: ' + r.status);
                    return r.json();
                })
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
    
    function markAsProcessed() {
        const id = document.getElementById('contactId').value;
        const formData = new FormData();
        formData.append('action', 'update_status');
        formData.append('id', id);
        formData.append('statut', 'traite');
        
        fetch('/kmservices/public/handlers/crud_contacts.php', {method: 'POST', body: formData})
            .then(r => {
                if (!r.ok) throw new Error('Erreur HTTP: ' + r.status);
                return r.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Message marqué comme traité');
                    closeContactModal();
                    location.reload();
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(err => console.error('Erreur:', err));
    }
    
    function toggleReplyForm() {
        const form = document.getElementById('replyForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
    
    function sendReply() {
        const id = document.getElementById('contactId').value;
        const email = document.getElementById('contactEmail').textContent;
        const nom = document.getElementById('contactNom').textContent;
        const message = document.getElementById('replyMessage').value;
        
        if (!message.trim()) {
            alert('Le message de réponse ne peut pas être vide');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'reply');
        formData.append('id', id);
        formData.append('email', email);
        formData.append('nom', nom);
        formData.append('message', message);
        
        fetch('/kmservices/public/handlers/crud_contacts.php', {method: 'POST', body: formData})
            .then(r => {
                if (!r.ok) throw new Error('Erreur HTTP: ' + r.status);
                return r.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Réponse envoyée avec succès');
                    document.getElementById('replyMessage').value = '';
                    toggleReplyForm();
                    markAsProcessed();
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(err => console.error('Erreur:', err));
    }
    
    window.onclick = function(event) {
        const modal = document.getElementById('contactModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    function escapeHtml(str) {
        return (str || '').replace(/[&<>"']/g, function(m) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m]);
        });
    }
    function linkifyAndEscape(text) {
        const escaped = escapeHtml(text).replace(/\n/g, '<br>');
        const urlRegex = /(https?:\/\/[^\s<]+)/g;
        return escaped.replace(urlRegex, function(url) {
            return '<a href="' + url + '" target="_blank" rel="noopener noreferrer">' + url + '</a>';
        });
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
        
        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
            <button onclick="closeContactModal()" style="padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">Fermer</button>
            <button onclick="toggleReplyForm()" style="padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Répondre</button>
            <button onclick="markAsProcessed()" style="padding: 8px 16px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">Marquer comme Traité</button>
            <button onclick="deleteContact(document.getElementById('contactId').value); closeContactModal();" style="padding: 8px 16px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">Supprimer</button>
        </div>
        
        <!-- Formulaire de Réponse (caché par défaut) -->
        <div id="replyForm" style="display: none; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e0e0e0;">
            <h3>Envoyer une Réponse</h3>
            <div style="margin-bottom: 1rem;">
                <label for="replyMessage" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Message de Réponse *</label>
                <textarea id="replyMessage" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-family: Arial, sans-serif;" rows="6" placeholder="Écrivez votre réponse ici..."></textarea>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button onclick="sendReply()" style="padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Envoyer la Réponse</button>
                <button onclick="toggleReplyForm()" style="padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">Annuler</button>
            </div>
        </div>
    </div>
</div>
