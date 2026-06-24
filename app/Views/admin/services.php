<?php
$title = 'Szolgáltatások — Bukta Zoltán EV Admin';
$extraCss = ['/css/admin.css'];
require APP_PATH . '/Views/partials/head.php';
?>
<body class="admin-page">
<?php require APP_PATH . '/Views/partials/sidebar.php'; ?>

<main class="admin-main">
  <div class="admin-topbar">
    <button class="burger-btn" id="sidebarToggle">☰</button>
    <h1 class="admin-page-title">Szolgáltatások kezelése</h1>
    <button class="btn btn-copper topbar-action" onclick="openModal()">+ Új szolgáltatás</button>
  </div>
  <div class="admin-content">
    <div class="admin-card">
      <div class="card-header"><h3>Szolgáltatások</h3></div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>Szín</th><th>Név</th><th>Leírás</th><th>Időtartam</th><th>Ár</th><th>Sorrend</th><th>Aktív</th><th>Műveletek</th></tr>
          </thead>
          <tbody id="servicesTbody">
            <tr><td colspan="8" class="table-loading">Betöltés...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<!-- Modal -->
<div class="modal-overlay" id="serviceOverlay" style="display:none">
  <div class="modal">
    <button class="modal-close" onclick="closeModal()">✕</button>
    <h3 id="modalTitle">Új szolgáltatás</h3>
    <input type="hidden" id="editId">
    <div class="form-group">
      <label>Név *</label>
      <input type="text" id="svcName" placeholder="Hajvágás">
    </div>
    <div class="form-group">
      <label>Leírás</label>
      <textarea id="svcDesc" rows="2" placeholder="Rövid leírás..."></textarea>
    </div>
    <div class="form-row" style="gap:14px;display:flex">
      <div class="form-group" style="flex:1">
        <label>Időtartam</label>
        <select id="svcDuration">
          <option value="30">30 perc</option>
          <option value="60" selected>60 perc</option>
        </select>
      </div>
      <div class="form-group" style="flex:1">
        <label>Ár (Ft)</label>
        <input type="number" id="svcPrice" placeholder="Egyedi">
      </div>
    </div>
    <div class="form-row" style="gap:14px;display:flex">
      <div class="form-group" style="flex:1">
        <label>Szín (naptárban)</label>
        <input type="color" id="svcColor" value="#B87333" style="height:42px;padding:4px">
      </div>
      <div class="form-group" style="flex:1">
        <label>Sorrend</label>
        <input type="number" id="svcSort" value="0" min="0">
      </div>
    </div>
    <div class="form-group">
      <label class="toggle-label" style="display:flex;align-items:center;gap:8px;cursor:pointer">
        <input type="checkbox" id="svcActive" checked style="accent-color:var(--copper)"> Aktív
      </label>
    </div>
    <div id="svcAlert" class="alert" style="display:none"></div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Mégsem</button>
      <button class="btn btn-copper" onclick="saveService()">Mentés</button>
    </div>
  </div>
</div>

<script src="/js/admin.js"></script>
<script>
async function loadServices() {
  const tbody = document.getElementById('servicesTbody');
  try {
    const res  = await fetch('/api/admin/szolgaltatasok');
    const data = await res.json();
    if (!data.services?.length) {
      tbody.innerHTML = '<tr><td colspan="8" class="table-loading">Nincs szolgáltatás.</td></tr>'; return;
    }
    tbody.innerHTML = data.services.map(s => `
      <tr>
        <td><span style="display:inline-block;width:18px;height:18px;border-radius:3px;background:${esc(s.color)}"></span></td>
        <td><strong>${esc(s.name)}</strong></td>
        <td style="font-size:12px;color:var(--muted)">${esc(s.description||'—')}</td>
        <td>${s.duration} perc</td>
        <td style="color:var(--copper)">${s.price ? Number(s.price).toLocaleString('hu-HU') + ' Ft' : 'Egyedi'}</td>
        <td>${s.sort_order}</td>
        <td><span class="badge ${s.is_active?'badge-active':'badge-inactive'}">${s.is_active?'Aktív':'Inaktív'}</span></td>
        <td>
          <div class="table-actions">
            <button class="action-btn" onclick='editService(${JSON.stringify(s)})'>Szerkesztés</button>
            <button class="action-btn danger" onclick="deleteService(${s.id})">Törlés</button>
          </div>
        </td>
      </tr>`).join('');
  } catch {
    tbody.innerHTML = '<tr><td colspan="8" class="table-loading">Betöltési hiba.</td></tr>';
  }
}

function openModal(reset=true) {
  if (reset) {
    document.getElementById('modalTitle').textContent = 'Új szolgáltatás';
    document.getElementById('editId').value = '';
    document.getElementById('svcName').value = '';
    document.getElementById('svcDesc').value = '';
    document.getElementById('svcDuration').value = '60';
    document.getElementById('svcPrice').value = '';
    document.getElementById('svcColor').value = '#B87333';
    document.getElementById('svcSort').value = '0';
    document.getElementById('svcActive').checked = true;
    document.getElementById('svcAlert').style.display = 'none';
  }
  document.getElementById('serviceOverlay').style.display = 'flex';
}
function closeModal() { document.getElementById('serviceOverlay').style.display = 'none'; }

function editService(s) {
  document.getElementById('modalTitle').textContent = 'Szerkesztés';
  document.getElementById('editId').value    = s.id;
  document.getElementById('svcName').value   = s.name;
  document.getElementById('svcDesc').value   = s.description || '';
  document.getElementById('svcDuration').value = s.duration;
  document.getElementById('svcPrice').value  = s.price || '';
  document.getElementById('svcColor').value  = s.color;
  document.getElementById('svcSort').value   = s.sort_order;
  document.getElementById('svcActive').checked = !!s.is_active;
  openModal(false);
}

async function saveService() {
  const alert = document.getElementById('svcAlert');
  alert.style.display = 'none';
  const body = new FormData();
  body.append('id',          document.getElementById('editId').value);
  body.append('name',        document.getElementById('svcName').value);
  body.append('description', document.getElementById('svcDesc').value);
  body.append('duration',    document.getElementById('svcDuration').value);
  body.append('price',       document.getElementById('svcPrice').value);
  body.append('color',       document.getElementById('svcColor').value);
  body.append('sort_order',  document.getElementById('svcSort').value);
  if (document.getElementById('svcActive').checked) body.append('is_active', '1');
  const res  = await fetch('/api/admin/szolgaltatas', { method: 'POST', body });
  const data = await res.json();
  if (data.success) { closeModal(); loadServices(); }
  else { alert.className = 'alert alert-error'; alert.textContent = data.message; alert.style.display = 'block'; }
}

async function deleteService(id) {
  if (!confirm('Biztosan deaktiválod ezt a szolgáltatást?')) return;
  const body = new FormData(); body.append('id', id);
  const res  = await fetch('/api/admin/szolgaltatas/torles', { method: 'POST', body });
  const data = await res.json();
  if (data.success) loadServices();
  else alert(data.message || 'Hiba.');
}

function esc(str) { const d=document.createElement('div');d.textContent=str||'';return d.innerHTML; }
document.addEventListener('DOMContentLoaded', loadServices);
</script>
</body>
</html>
