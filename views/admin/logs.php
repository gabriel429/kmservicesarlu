<?php
$adminPageTitle = 'Journal des actions';
?>
<div class="card">
  <h2>Journal des actions</h2>
  <div class="form-row" style="display:flex; gap:1rem; margin-bottom:1rem;">
    <select id="logType">
      <option value="users">Utilisateurs</option>
      <option value="products">Produits</option>
      <option value="projects">Projets</option>
      <option value="contacts">Contacts</option>
      <option value="security">Sécurité (Accès interdits)</option>
    </select>
    <button id="refreshBtn" class="btn-small btn-primary">Actualiser</button>
  </div>
  <table class="admin-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Admin</th>
        <th>Cible</th>
        <th>Action</th>
        <th>Détails</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody id="logsBody"></tbody>
  </table>
</div>
<script>
async function loadLogs() {
  const type = document.getElementById('logType').value;
  const res = await fetch('<?php echo APP_URL; ?>handlers/audit.php?type=' + encodeURIComponent(type) + '&limit=50');
  const text = await res.text();
  let data;
  try { data = JSON.parse(text); } catch { data = { success:false, message:'Réponse invalide' }; }
  const body = document.getElementById('logsBody');
  body.innerHTML = '';
  if (!data.success) {
    body.innerHTML = '<tr><td colspan="6">Erreur: ' + (data.message || 'Impossible de charger les logs') + '</td></tr>';
    return;
  }
  const rows = data.data || [];
  if (rows.length === 0) {
    body.innerHTML = '<tr><td colspan="6">Aucune entrée</td></tr>';
    return;
  }
  const typeKeyMap = { users:'target_user_id', products:'product_id', projects:'project_id', contacts:'contact_id', security:null };
  const key = typeKeyMap[type];
  for (const r of rows) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td data-label="ID">${r.id}</td>
      <td data-label="Admin">${r.admin_user_id ?? ''}</td>
      <td data-label="Cible">${key ? (r[key] ?? '') : ''}</td>
      <td data-label="Action">${r.action}</td>
      <td data-label="Détails">${r.details ? r.details : ''}</td>
      <td data-label="Date">${r.created_at}</td>
    `;
    body.appendChild(tr);
  }
}

document.getElementById('refreshBtn').addEventListener('click', loadLogs);
loadLogs();
</script>
