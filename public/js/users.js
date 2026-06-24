let allUsers = [];

async function loadUsers() {
  const tbody = document.getElementById('usersTbody');
  if (!tbody) return;
  try {
    const res  = await fetch('/api/admin/felhasznalok');
    const data = await res.json();
    allUsers   = data.users || [];
    renderUsers(allUsers);
  } catch {
    tbody.innerHTML = '<tr><td colspan="6" class="table-loading">Betöltési hiba.</td></tr>';
  }
}

function renderUsers(users) {
  const tbody = document.getElementById('usersTbody');
  const count = document.getElementById('tableCount');
  if (!tbody) return;
  if (count) count.textContent = `${users.length} felhasználó`;
  if (!users.length) {
    tbody.innerHTML = '<tr><td colspan="6" class="table-loading">Nincs találat.</td></tr>';
    return;
  }
  tbody.innerHTML = users.map(u => `
    <tr>
      <td>${esc(u.name)}</td>
      <td>${esc(u.email)}</td>
      <td><span class="badge badge-${u.role}">${u.role === 'admin' ? 'Admin' : 'Felhasználó'}</span></td>
      <td><span class="badge badge-${u.status}">${u.status === 'active' ? 'Aktív' : 'Inaktív'}</span></td>
      <td>${formatDate(u.created_at)}</td>
      <td>
        <div class="table-actions">
          ${u.status === 'active'
            ? `<button class="action-btn danger" onclick="confirmToggle(${u.id},'inactive','${esc(u.name)}')">Deaktivál</button>`
            : `<button class="action-btn success" onclick="confirmToggle(${u.id},'active','${esc(u.name)}')">Aktivál</button>`}
          ${u.role !== 'admin'
            ? `<button class="action-btn" onclick="confirmRole(${u.id},'admin','${esc(u.name)}')">Admin legyen</button>`
            : `<button class="action-btn danger" onclick="confirmRole(${u.id},'user','${esc(u.name)}')">Rang elvesz</button>`}
        </div>
      </td>
    </tr>
  `).join('');
}

// ---- FILTERS ----
function applyFilters() {
  const search = (document.getElementById('searchUsers')?.value || '').toLowerCase();
  const role   = document.getElementById('filterRole')?.value || '';
  const status = document.getElementById('filterStatus')?.value || '';
  renderUsers(allUsers.filter(u =>
    (!search || u.name.toLowerCase().includes(search) || u.email.toLowerCase().includes(search)) &&
    (!role   || u.role   === role) &&
    (!status || u.status === status)
  ));
}
['searchUsers','filterRole','filterStatus'].forEach(id => {
  document.getElementById(id)?.addEventListener('input', applyFilters);
});

// ---- CONFIRM MODAL ----
let pendingAction = null;
function showConfirm(title, msg, action) {
  document.getElementById('confirmTitle').textContent = title;
  document.getElementById('confirmMsg').textContent   = msg;
  document.getElementById('confirmOverlay').style.display = 'flex';
  pendingAction = action;
}
document.getElementById('confirmCancel')?.addEventListener('click', () => {
  document.getElementById('confirmOverlay').style.display = 'none';
  pendingAction = null;
});
document.getElementById('confirmOk')?.addEventListener('click', async () => {
  document.getElementById('confirmOverlay').style.display = 'none';
  if (pendingAction) await pendingAction();
  pendingAction = null;
});

function confirmToggle(id, newStatus, name) {
  const label = newStatus === 'active' ? 'aktiválod' : 'deaktiválod';
  showConfirm('Státusz módosítás', `Biztosan ${label} ${name} fiókját?`, () => updateUser(id, { status: newStatus }));
}
function confirmRole(id, newRole, name) {
  const label = newRole === 'admin' ? 'admin jogot adsz' : 'elveszed az admin jogot';
  showConfirm('Szerepkör módosítás', `Biztosan ${label} ${name} számára?`, () => updateUser(id, { role: newRole }));
}

async function updateUser(id, payload) {
  try {
    const body = new FormData();
    body.append('id', id);
    Object.entries(payload).forEach(([k, v]) => body.append(k, v));
    const res  = await fetch('/api/admin/felhasznalo', { method: 'POST', body });
    const data = await res.json();
    if (data.success) loadUsers();
    else alert(data.message || 'Hiba történt.');
  } catch { alert('Szerver hiba.'); }
}

function esc(str) {
  const d = document.createElement('div');
  d.textContent = str || '';
  return d.innerHTML;
}
function formatDate(str) {
  if (!str) return '—';
  return new Date(str).toLocaleDateString('hu-HU', { year: 'numeric', month: 'short', day: 'numeric' });
}

document.addEventListener('DOMContentLoaded', loadUsers);
