<?php
$title = 'Foglalások — Bukta Zoltán EV Admin';
$extraCss = ['/css/admin.css'];
require APP_PATH . '/Views/partials/head.php';
?>
<body class="admin-page">
<?php require APP_PATH . '/Views/partials/sidebar.php'; ?>

<main class="admin-main">
  <div class="admin-topbar">
    <button class="burger-btn" id="sidebarToggle">☰</button>
    <h1 class="admin-page-title">Foglalások kezelése</h1>
  </div>
  <div class="admin-content">

    <div class="admin-card">
      <div class="card-header">
        <h3>Foglalások</h3>
        <div class="filter-row">
          <select id="filterStatus" class="filter-select" onchange="loadBookings()">
            <option value="">Minden státusz</option>
            <option value="pending">Várakozik</option>
            <option value="confirmed">Megerősítve</option>
            <option value="cancelled">Lemondva</option>
            <option value="rejected">Elutasítva</option>
          </select>
          <input type="date" id="filterFrom" class="search-input" placeholder="Dátumtól" onchange="loadBookings()" style="width:150px">
          <input type="date" id="filterTo" class="search-input" placeholder="Dátumig" onchange="loadBookings()" style="width:150px">
        </div>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>Ügyfél</th><th>Szolgáltatás</th><th>Időpont</th><th>Státusz</th><th>Megjegyzés</th><th>Műveletek</th></tr>
          </thead>
          <tbody id="bookingsTbody">
            <tr><td colspan="6" class="table-loading">Betöltés...</td></tr>
          </tbody>
        </table>
      </div>
      <div class="table-footer"><span id="bookingsCount" class="table-count"></span></div>
    </div>

  </div>
</main>

<!-- Reject modal -->
<div class="modal-overlay" id="rejectOverlay" style="display:none">
  <div class="modal modal-sm">
    <button class="modal-close" onclick="closeReject()">✕</button>
    <h3>Elutasítás</h3>
    <p>Indoklás (opcionális, e-mailben elküldésre kerül az ügyfélnek):</p>
    <div class="form-group">
      <textarea id="rejectNote" rows="3" placeholder="Pl. az időpont nem megfelelő..."></textarea>
    </div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeReject()">Mégsem</button>
      <button class="btn btn-danger" onclick="doReject()">Elutasítás</button>
    </div>
  </div>
</div>

<script src="/js/admin.js"></script>
<script>
let rejectId = null;

async function loadBookings() {
  const tbody  = document.getElementById('bookingsTbody');
  const status = document.getElementById('filterStatus').value;
  const from   = document.getElementById('filterFrom').value;
  const to     = document.getElementById('filterTo').value;
  tbody.innerHTML = '<tr><td colspan="6" class="table-loading">Betöltés...</td></tr>';

  let url = '/api/admin/foglalasok?';
  if (status) url += `status=${status}&`;
  if (from)   url += `date_from=${from}&`;
  if (to)     url += `date_to=${to}&`;

  try {
    const res  = await fetch(url);
    const data = await res.json();
    const bookings = data.bookings || [];
    document.getElementById('bookingsCount').textContent = bookings.length + ' foglalás';

    if (!bookings.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="table-loading">Nincs találat.</td></tr>';
      return;
    }
    tbody.innerHTML = bookings.map(b => {
      const date = new Date(b.booking_date).toLocaleDateString('hu-HU',{year:'numeric',month:'short',day:'numeric'});
      const time = b.booking_time.substring(0,5);
      const statMap = {pending:'Várakozik',confirmed:'Megerősítve',cancelled:'Lemondva',rejected:'Elutasítva'};
      const statBadge = {pending:'badge-pending',confirmed:'badge-active',cancelled:'badge-inactive',rejected:'badge-inactive'};
      const isPending = b.status === 'pending';
      const isConfirmed = b.status === 'confirmed';
      const recurring = b.is_recurring ? ' 🔁' : '';
      return `<tr>
        <td><strong>${esc(b.user_name)}</strong><br><span style="font-size:11px;color:var(--muted)">${esc(b.user_email)}</span></td>
        <td><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:${esc(b.service_color)};margin-right:6px"></span>${esc(b.service_name)}${recurring}<br><span style="font-size:11px;color:var(--muted)">${b.service_duration} perc</span></td>
        <td><strong>${date}</strong><br>${time}</td>
        <td><span class="badge ${statBadge[b.status]}">${statMap[b.status]||b.status}</span></td>
        <td style="font-size:12px;color:var(--muted);max-width:160px">${esc(b.note||'—')}</td>
        <td>
          <div class="table-actions">
            ${isPending ? `<button class="action-btn success" onclick="confirmBooking(${b.id})">Jóváhagyás</button>` : ''}
            ${isPending || isConfirmed ? `<button class="action-btn danger" onclick="openReject(${b.id})">Elutasít</button>` : ''}
          </div>
        </td>
      </tr>`;
    }).join('');
  } catch {
    tbody.innerHTML = '<tr><td colspan="6" class="table-loading">Betöltési hiba.</td></tr>';
  }
}

async function confirmBooking(id) {
  if (!confirm('Jóváhagyod ezt a foglalást? Az ügyfél e-mail értesítést kap.')) return;
  const body = new FormData(); body.append('id', id);
  const res  = await fetch('/api/admin/foglalas/jovahagyas', { method: 'POST', body });
  const data = await res.json();
  if (data.success) loadBookings();
  else alert(data.message || 'Hiba történt.');
}

function openReject(id) { rejectId = id; document.getElementById('rejectNote').value = ''; document.getElementById('rejectOverlay').style.display = 'flex'; }
function closeReject() { rejectId = null; document.getElementById('rejectOverlay').style.display = 'none'; }

async function doReject() {
  if (!rejectId) return;
  const body = new FormData();
  body.append('id', rejectId);
  body.append('admin_note', document.getElementById('rejectNote').value);
  const res  = await fetch('/api/admin/foglalas/elutasit', { method: 'POST', body });
  const data = await res.json();
  closeReject();
  if (data.success) loadBookings();
  else alert(data.message || 'Hiba történt.');
}

function esc(str) { const d=document.createElement('div');d.textContent=str||'';return d.innerHTML; }
document.addEventListener('DOMContentLoaded', loadBookings);
</script>
</body>
</html>
