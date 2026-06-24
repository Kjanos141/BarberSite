// ---- INVITE FORM ----
const inviteForm = document.getElementById('inviteForm');
const inviteResult = document.getElementById('inviteResult');

if (inviteForm) {
  inviteForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = inviteForm.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Küldés...';
    inviteResult.style.display = 'none';

    const data = new FormData(inviteForm);
    try {
      const res = await fetch('php/invite.php', { method: 'POST', body: data });
      const json = await res.json();
      inviteResult.style.display = 'block';
      if (json.success) {
        inviteResult.className = 'alert alert-success';
        inviteResult.textContent = `Meghívó sikeresen elküldve: ${data.get('email')}`;
        inviteForm.reset();
        loadPendingInvites();
      } else {
        inviteResult.className = 'alert alert-error';
        inviteResult.textContent = json.message || 'Hiba történt.';
      }
    } catch {
      inviteResult.style.display = 'block';
      inviteResult.className = 'alert alert-error';
      inviteResult.textContent = 'Szerver hiba. Kérjük próbálja újra.';
    }
    btn.disabled = false;
    btn.textContent = 'Meghívó elküldése';
  });
}

// ---- PENDING INVITES ----
async function loadPendingInvites() {
  const tbody = document.getElementById('pendingInvites');
  if (!tbody) return;
  try {
    const res = await fetch('php/invites.php');
    const data = await res.json();
    if (!data.invites || !data.invites.length) {
      tbody.innerHTML = '<tr><td colspan="5" class="table-loading">Nincs függő meghívó.</td></tr>';
      return;
    }
    tbody.innerHTML = data.invites.map(inv => `
      <tr>
        <td>${esc(inv.email)}</td>
        <td><span class="badge badge-${inv.role}">${inv.role === 'admin' ? 'Admin' : 'Felhasználó'}</span></td>
        <td>${formatDate(inv.created_at)}</td>
        <td>${formatDate(inv.expires_at)}</td>
        <td>
          <button class="action-btn danger" onclick="revokeInvite(${inv.id})">Visszavon</button>
          <button class="action-btn" onclick="resendInvite(${inv.id})">Újraküld</button>
        </td>
      </tr>
    `).join('');
  } catch {
    tbody.innerHTML = '<tr><td colspan="5" class="table-loading">Betöltési hiba.</td></tr>';
  }
}

async function revokeInvite(id) {
  if (!confirm('Biztosan visszavonod ezt a meghívót?')) return;
  const body = new FormData();
  body.append('id', id);
  body.append('action', 'revoke');
  const res = await fetch('php/manage_invite.php', { method: 'POST', body });
  const data = await res.json();
  if (data.success) loadPendingInvites();
  else alert(data.message || 'Hiba történt.');
}

async function resendInvite(id) {
  const body = new FormData();
  body.append('id', id);
  body.append('action', 'resend');
  const res = await fetch('php/manage_invite.php', { method: 'POST', body });
  const data = await res.json();
  alert(data.success ? 'Meghívó újraküldve.' : (data.message || 'Hiba történt.'));
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

document.addEventListener('DOMContentLoaded', loadPendingInvites);
