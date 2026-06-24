// ---- SIDEBAR TOGGLE ----
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
if (sidebarToggle && sidebar) {
  sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
  document.addEventListener('click', (e) => {
    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
      sidebar.classList.remove('open');
    }
  });
}

// ---- HELPERS ----
function esc(str) {
  const d = document.createElement('div');
  d.textContent = str || '';
  return d.innerHTML;
}
function formatDate(str) {
  if (!str) return '—';
  return new Date(str).toLocaleDateString('hu-HU', { year: 'numeric', month: 'short', day: 'numeric' });
}

// ---- LOAD STATS ----
async function loadStats() {
  try {
    const res  = await fetch('/api/admin/stats');
    const data = await res.json();
    const set  = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val ?? '0'; };
    set('statTotal',   data.total);
    set('statActive',  data.active);
    set('statPending', data.pending_invites);
    set('statAdmins',  data.admins);
  } catch (e) { console.warn('Stats betöltési hiba:', e); }
}

// ---- LOAD RECENT USERS ----
async function loadRecentUsers() {
  const tbody = document.querySelector('#recentUsersTable tbody');
  if (!tbody) return;
  try {
    const res  = await fetch('/api/admin/felhasznalok?limit=5');
    const data = await res.json();
    if (!data.users?.length) {
      tbody.innerHTML = '<tr><td colspan="5" class="table-loading">Nincs felhasználó.</td></tr>';
      return;
    }
    tbody.innerHTML = data.users.map(u => `
      <tr>
        <td>${esc(u.name)}</td>
        <td>${esc(u.email)}</td>
        <td><span class="badge badge-${u.role}">${u.role === 'admin' ? 'Admin' : 'Felhasználó'}</span></td>
        <td><span class="badge badge-${u.status}">${u.status === 'active' ? 'Aktív' : 'Inaktív'}</span></td>
        <td>${formatDate(u.created_at)}</td>
      </tr>
    `).join('');
  } catch {
    tbody.innerHTML = '<tr><td colspan="5" class="table-loading">Betöltési hiba.</td></tr>';
  }
}

// ---- QUICK INVITE ----
const quickInviteBtn = document.getElementById('quickInviteBtn');
const inviteMsg      = document.getElementById('inviteMsg');
if (quickInviteBtn) {
  quickInviteBtn.addEventListener('click', async () => {
    quickInviteBtn.disabled = true;
    quickInviteBtn.textContent = 'Küldés...';
    inviteMsg.style.display = 'none';

    const body = new FormData();
    body.append('email', document.getElementById('inviteEmail').value);
    body.append('role',  document.getElementById('inviteRole').value);

    try {
      const res  = await fetch('/api/admin/meghivo', { method: 'POST', body });
      const json = await res.json();
      inviteMsg.style.display = 'block';
      if (json.success) {
        inviteMsg.className = 'alert alert-success';
        inviteMsg.textContent = `Meghívó elküldve: ${document.getElementById('inviteEmail').value}`;
        document.getElementById('inviteEmail').value = '';
      } else {
        inviteMsg.className = 'alert alert-error';
        inviteMsg.textContent = json.message || 'Hiba történt.';
      }
    } catch {
      inviteMsg.style.display = 'block';
      inviteMsg.className = 'alert alert-error';
      inviteMsg.textContent = 'Szerver hiba. Kérjük próbálja újra.';
    }
    quickInviteBtn.disabled = false;
    quickInviteBtn.textContent = 'Meghívó küldése';
  });
}

// ---- INIT ----
document.addEventListener('DOMContentLoaded', () => {
  loadStats();
  loadRecentUsers();
});
