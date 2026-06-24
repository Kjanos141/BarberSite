// ---- SIDEBAR TOGGLE ----
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
if (sidebarToggle && sidebar) {
  sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
  });
  // Close on outside click
  document.addEventListener('click', (e) => {
    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
      sidebar.classList.remove('open');
    }
  });
}

// ---- MODAL HELPERS ----
const modalOverlay = document.getElementById('modalOverlay');
const modalClose = document.getElementById('modalClose');
if (modalClose) {
  modalClose.addEventListener('click', () => {
    if (modalOverlay) modalOverlay.style.display = 'none';
  });
}
if (modalOverlay) {
  modalOverlay.addEventListener('click', (e) => {
    if (e.target === modalOverlay) modalOverlay.style.display = 'none';
  });
}

// ---- LOAD DASHBOARD STATS ----
async function loadStats() {
  try {
    const res = await fetch('php/stats.php');
    const data = await res.json();
    const set = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.textContent = val ?? '0';
    };
    set('statTotal', data.total);
    set('statActive', data.active);
    set('statPending', data.pending_invites);
    set('statAdmins', data.admins);
  } catch (e) {
    console.warn('Stats betöltési hiba:', e);
  }
}

// ---- LOAD RECENT USERS ----
async function loadRecentUsers() {
  const tbody = document.querySelector('#recentUsersTable tbody');
  if (!tbody) return;
  try {
    const res = await fetch('php/users.php?limit=5');
    const data = await res.json();
    if (!data.users || !data.users.length) {
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
  } catch (e) {
    tbody.innerHTML = '<tr><td colspan="5" class="table-loading">Betöltési hiba.</td></tr>';
  }
}

// ---- QUICK INVITE ----
const quickInviteForm = document.getElementById('quickInviteForm');
const inviteMsg = document.getElementById('inviteMsg');
if (quickInviteForm) {
  quickInviteForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = quickInviteForm.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Küldés...';
    inviteMsg.style.display = 'none';
    const data = new FormData(quickInviteForm);
    try {
      const res = await fetch('php/invite.php', { method: 'POST', body: data });
      const json = await res.json();
      inviteMsg.style.display = 'block';
      if (json.success) {
        inviteMsg.className = 'alert alert-success';
        inviteMsg.textContent = `Meghívó elküldve: ${document.getElementById('inviteEmail').value}`;
        quickInviteForm.reset();
      } else {
        inviteMsg.className = 'alert alert-error';
        inviteMsg.textContent = json.message || 'Hiba történt.';
      }
    } catch {
      inviteMsg.style.display = 'block';
      inviteMsg.className = 'alert alert-error';
      inviteMsg.textContent = 'Szerver hiba. Kérjük próbálja újra.';
    }
    btn.disabled = false;
    btn.textContent = 'Meghívó küldése';
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
  const d = new Date(str);
  return d.toLocaleDateString('hu-HU', { year: 'numeric', month: 'short', day: 'numeric' });
}

// ---- INIT ----
document.addEventListener('DOMContentLoaded', () => {
  loadStats();
  loadRecentUsers();
});
