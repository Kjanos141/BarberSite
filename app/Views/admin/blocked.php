<?php
$title = 'Letiltott időpontok — Bukta Zoltán EV Admin';
$extraCss = ['/css/admin.css'];
require APP_PATH . '/Views/partials/head.php';
?>
<body class="admin-page">
<?php require APP_PATH . '/Views/partials/sidebar.php'; ?>

<main class="admin-main">
  <div class="admin-topbar">
    <button class="burger-btn" id="sidebarToggle">☰</button>
    <h1 class="admin-page-title">Letiltott napok / időpontok</h1>
    <button class="btn btn-copper topbar-action" onclick="openModal()">+ Letiltás hozzáadása</button>
  </div>
  <div class="admin-content">
    <div class="admin-card">
      <div class="card-header"><h3>Aktív letiltások</h3></div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>Típus</th><th>Részletek</th><th>Indok</th><th>Érvényes</th><th>Létrehozta</th><th>Műveletek</th></tr>
          </thead>
          <tbody id="blockedTbody">
            <tr><td colspan="6" class="table-loading">Betöltés...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<!-- Modal -->
<div class="modal-overlay" id="blockedOverlay" style="display:none">
  <div class="modal">
    <button class="modal-close" onclick="closeModal()">✕</button>
    <h3>Letiltás hozzáadása</h3>

    <div class="form-group">
      <label>Típus *</label>
      <select id="blockType" onchange="updateFields()">
        <option value="day">Egyszeri nap letiltása</option>
        <option value="slot">Egyszeri időpont letiltása</option>
        <option value="recurring_day">Ismétlődő nap (pl. minden hétvége)</option>
        <option value="recurring_slot">Ismétlődő időpont egy adott napon</option>
      </select>
    </div>

    <div id="fieldDate" class="form-group">
      <label>Dátum *</label>
      <input type="date" id="blockDate">
    </div>

    <div id="fieldTime" class="form-group" style="display:none">
      <label>Időpont *</label>
      <input type="time" id="blockTime" step="1800">
    </div>

    <div id="fieldWeekday" class="form-group" style="display:none">
      <label>Hét napja *</label>
      <select id="blockWeekday">
        <option value="0">Vasárnap</option>
        <option value="1">Hétfő</option>
        <option value="2">Kedd</option>
        <option value="3">Szerda</option>
        <option value="4">Csütörtök</option>
        <option value="5">Péntek</option>
        <option value="6">Szombat</option>
      </select>
    </div>

    <div id="fieldRecTime" class="form-group" style="display:none">
      <label>Ismétlődő időpont *</label>
      <input type="time" id="blockRecTime" step="1800">
    </div>

    <div id="fieldValidity" style="display:none">
      <div class="form-row" style="gap:14px;display:flex">
        <div class="form-group" style="flex:1">
          <label>Érvényes-tól</label>
          <input type="date" id="blockFrom">
        </div>
        <div class="form-group" style="flex:1">
          <label>Érvényes-ig</label>
          <input type="date" id="blockUntil">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>Indok (opcionális)</label>
      <input type="text" id="blockReason" placeholder="pl. Ünnepnap, szabadság...">
    </div>

    <div id="blockAlert" class="alert" style="display:none"></div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Mégsem</button>
      <button class="btn btn-copper" onclick="saveBlock()">Mentés</button>
    </div>
  </div>
</div>

<script src="/js/admin.js"></script>
<script>
const typeLabels = {
  day:'Egyszeri nap', slot:'Egyszeri időpont',
  recurring_day:'Ismétlődő nap', recurring_slot:'Ismétlődő időpont'
};
const weekdays = ['Vasárnap','Hétfő','Kedd','Szerda','Csütörtök','Péntek','Szombat'];

async function loadBlocked() {
  const tbody = document.getElementById('blockedTbody');
  try {
    const res  = await fetch('/api/admin/letiltasok');
    const data = await res.json();
    if (!data.blocked?.length) { tbody.innerHTML = '<tr><td colspan="6" class="table-loading">Nincs letiltás.</td></tr>'; return; }
    tbody.innerHTML = data.blocked.map(b => {
      let detail = '';
      if (b.block_type === 'day')            detail = `Dátum: ${b.block_date}`;
      if (b.block_type === 'slot')           detail = `${b.block_date} ${b.block_time?.substring(0,5)||''}`;
      if (b.block_type === 'recurring_day')  detail = weekdays[b.weekday] ?? '';
      if (b.block_type === 'recurring_slot') detail = `${weekdays[b.weekday]}, ${b.recurring_time?.substring(0,5)||''}`;
      const validity = (b.valid_from || b.valid_until)
        ? `${b.valid_from||'—'} → ${b.valid_until||'—'}` : 'Mindig';
      return `<tr>
        <td><span class="badge badge-pending">${typeLabels[b.block_type]||b.block_type}</span></td>
        <td>${esc(detail)}</td>
        <td style="color:var(--muted);font-size:12px">${esc(b.reason||'—')}</td>
        <td style="font-size:12px">${esc(validity)}</td>
        <td style="font-size:12px;color:var(--muted)">${esc(b.created_by_name||'—')}</td>
        <td><button class="action-btn danger" onclick="deleteBlock(${b.id})">Törlés</button></td>
      </tr>`;
    }).join('');
  } catch { tbody.innerHTML = '<tr><td colspan="6" class="table-loading">Betöltési hiba.</td></tr>'; }
}

function openModal() { document.getElementById('blockedOverlay').style.display='flex'; updateFields(); }
function closeModal() { document.getElementById('blockedOverlay').style.display='none'; }

function updateFields() {
  const type = document.getElementById('blockType').value;
  document.getElementById('fieldDate').style.display    = ['day','slot'].includes(type) ? 'block' : 'none';
  document.getElementById('fieldTime').style.display    = type === 'slot' ? 'block' : 'none';
  document.getElementById('fieldWeekday').style.display = type.startsWith('recurring') ? 'block' : 'none';
  document.getElementById('fieldRecTime').style.display = type === 'recurring_slot' ? 'block' : 'none';
  document.getElementById('fieldValidity').style.display = type.startsWith('recurring') ? 'block' : 'none';
}

async function saveBlock() {
  const type = document.getElementById('blockType').value;
  const body = new FormData();
  body.append('block_type', type);
  body.append('reason', document.getElementById('blockReason').value);
  if (['day','slot'].includes(type))       body.append('block_date', document.getElementById('blockDate').value);
  if (type === 'slot')                     body.append('block_time', document.getElementById('blockTime').value + ':00');
  if (type.startsWith('recurring'))        body.append('weekday', document.getElementById('blockWeekday').value);
  if (type === 'recurring_slot')           body.append('recurring_time', document.getElementById('blockRecTime').value + ':00');
  if (type.startsWith('recurring')) {
    body.append('valid_from',  document.getElementById('blockFrom').value || '');
    body.append('valid_until', document.getElementById('blockUntil').value || '');
  }
  const alertEl = document.getElementById('blockAlert');
  const res  = await fetch('/api/admin/letiltasok', { method: 'POST', body });
  const data = await res.json();
  if (data.success) { closeModal(); loadBlocked(); }
  else { alertEl.className='alert alert-error'; alertEl.textContent=data.message||'Hiba.'; alertEl.style.display='block'; }
}

async function deleteBlock(id) {
  if (!confirm('Törlöd ezt a letiltást?')) return;
  const body = new FormData(); body.append('id', id);
  const res  = await fetch('/api/admin/letiltas/torles', { method: 'POST', body });
  const data = await res.json();
  if (data.success) loadBlocked();
  else alert(data.message || 'Hiba.');
}

function esc(str) { const d=document.createElement('div');d.textContent=str||'';return d.innerHTML; }
document.addEventListener('DOMContentLoaded', loadBlocked);
</script>
</body>
</html>
