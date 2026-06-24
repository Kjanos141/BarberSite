<?php
$title = 'Tevékenységnapló — Bukta Zoltán EV Admin';
$extraCss = ['/css/admin.css'];
require APP_PATH . '/Views/partials/head.php';
?>
<body class="admin-page">
<?php require APP_PATH . '/Views/partials/sidebar.php'; ?>

<main class="admin-main">
  <div class="admin-topbar">
    <button class="burger-btn" id="sidebarToggle">☰</button>
    <h1 class="admin-page-title">Tevékenységnapló</h1>
  </div>
  <div class="admin-content">
    <div class="admin-card">
      <div class="card-header">
        <h3>Napló bejegyzések</h3>
        <div class="filter-row">
          <select id="logLimit" class="filter-select" onchange="loadLog()">
            <option value="50">Utolsó 50</option>
            <option value="100" selected>Utolsó 100</option>
            <option value="250">Utolsó 250</option>
            <option value="500">Utolsó 500</option>
          </select>
        </div>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>Idő</th><th>Felhasználó</th><th>Esemény</th><th>Részletek</th><th>IP</th></tr>
          </thead>
          <tbody id="logTbody">
            <tr><td colspan="5" class="table-loading">Betöltés...</td></tr>
          </tbody>
        </table>
      </div>
      <div class="table-footer"><span id="logCount" class="table-count"></span></div>
    </div>
  </div>
</main>

<script src="/js/admin.js"></script>
<script>
const actionLabels = {
  'booking.create':       '📅 Foglalás létrehozva',
  'booking.confirm':      '✅ Foglalás jóváhagyva',
  'booking.reject':       '❌ Foglalás elutasítva',
  'booking.cancel':       '↩️ Foglalás lemondva',
  'booking.cancel_group': '↩️ Csoportos lemondás',
  'service.create':       '➕ Szolgáltatás létrehozva',
  'service.update':       '✏️ Szolgáltatás szerkesztve',
  'service.delete':       '🗑️ Szolgáltatás törölve',
  'blocked.create':       '🚫 Letiltás hozzáadva',
  'blocked.delete':       '✔️ Letiltás törölve',
  'invite.send':          '✉️ Meghívó küldve',
  'user.login':           '🔑 Bejelentkezés',
  'mail.error':           '⚠️ E-mail hiba',
};

async function loadLog() {
  const tbody = document.getElementById('logTbody');
  const limit = document.getElementById('logLimit').value;
  tbody.innerHTML = '<tr><td colspan="5" class="table-loading">Betöltés...</td></tr>';
  try {
    const res  = await fetch(`/api/admin/naplo?limit=${limit}`);
    const data = await res.json();
    const logs = data.logs || [];
    document.getElementById('logCount').textContent = logs.length + ' bejegyzés';
    if (!logs.length) { tbody.innerHTML = '<tr><td colspan="5" class="table-loading">Nincs bejegyzés.</td></tr>'; return; }
    tbody.innerHTML = logs.map(l => {
      const dt = new Date(l.created_at).toLocaleString('hu-HU');
      const label = actionLabels[l.action] || l.action;
      return `<tr>
        <td style="font-size:12px;white-space:nowrap">${dt}</td>
        <td style="font-size:13px">${esc(l.user_name||'Rendszer')}</td>
        <td>${esc(label)}</td>
        <td style="font-size:12px;color:var(--muted);max-width:260px">${esc(l.description||'—')}</td>
        <td style="font-size:11px;color:var(--muted)">${esc(l.ip_address||'—')}</td>
      </tr>`;
    }).join('');
  } catch { tbody.innerHTML = '<tr><td colspan="5" class="table-loading">Betöltési hiba.</td></tr>'; }
}

function esc(str) { const d=document.createElement('div');d.textContent=str||'';return d.innerHTML; }
document.addEventListener('DOMContentLoaded', loadLog);
</script>
</body>
</html>
