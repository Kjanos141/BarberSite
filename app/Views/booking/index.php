<?php
$title = 'Időpontfoglalás — Bukta Zoltán EV';
require APP_PATH . '/Views/partials/head.php';
?>
<style>
/* ---- BOOKING PAGE ---- */
.booking-layout {
  max-width: 1060px;
  margin: 0 auto;
  padding: calc(var(--nav-h) + 40px) 24px 80px;
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 32px;
  align-items: start;
}
.booking-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
}
.booking-card-header {
  padding: 22px 26px;
  border-bottom: 1px solid var(--border);
}
.booking-card-header h2 {
  font-family: var(--font-display);
  font-size: 20px;
  font-weight: 400;
  margin-bottom: 4px;
}
.booking-card-header p { font-size: 13px; color: var(--muted); }
.booking-card-body { padding: 26px; }

/* Services */
.service-list { display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px; }
.service-option {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  cursor: pointer;
  transition: all var(--transition);
}
.service-option:hover { border-color: var(--copper-dim); background: var(--surface2); }
.service-option.selected { border-color: var(--copper); background: rgba(184,115,51,0.06); }
.service-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.service-info { flex: 1; }
.service-info strong { font-size: 14px; display: block; margin-bottom: 2px; }
.service-info span { font-size: 12px; color: var(--muted); }
.service-meta { text-align: right; font-size: 12px; color: var(--copper); font-weight: 600; }

/* Calendar */
.calendar-nav {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 14px;
}
.cal-nav-btn {
  background: none; border: 1px solid var(--border);
  color: var(--muted); padding: 5px 12px; border-radius: var(--radius);
  font-size: 16px; transition: all var(--transition);
}
.cal-nav-btn:hover { color: var(--white); border-color: var(--muted); }
.cal-month { font-family: var(--font-display); font-size: 17px; }
.calendar-grid {
  display: grid; grid-template-columns: repeat(7,1fr); gap: 4px;
  margin-bottom: 20px;
}
.cal-head {
  font-size: 10px; font-weight: 700; letter-spacing: 0.1em;
  color: var(--muted); text-align: center; padding: 4px 0; text-transform: uppercase;
}
.cal-day {
  aspect-ratio: 1; display: flex; align-items: center; justify-content: center;
  font-size: 13px; border-radius: var(--radius); cursor: pointer;
  transition: all var(--transition); border: 1px solid transparent;
  position: relative;
}
.cal-day.empty { cursor: default; }
.cal-day.past, .cal-day.blocked { color: var(--border); cursor: not-allowed; }
.cal-day.available:hover { border-color: var(--copper-dim); background: rgba(184,115,51,0.06); }
.cal-day.selected { background: var(--copper); color: var(--ink); font-weight: 700; }
.cal-day.today { border-color: var(--copper-dim); }

/* Slots */
.slots-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 8px; margin-bottom: 24px; }
.slot-btn {
  padding: 10px 6px; font-size: 13px; font-weight: 600; text-align: center;
  border: 1px solid var(--border); border-radius: var(--radius);
  background: var(--surface2); color: var(--muted);
  cursor: pointer; transition: all var(--transition);
}
.slot-btn.available { color: var(--white); }
.slot-btn.available:hover { border-color: var(--copper-dim); background: rgba(184,115,51,0.08); }
.slot-btn.selected { background: var(--copper); border-color: var(--copper); color: var(--ink); }
.slot-btn.taken { opacity: 0.35; cursor: not-allowed; }
.slot-btn.blocked { opacity: 0.25; cursor: not-allowed; }

/* Recurring */
.recurring-wrap { margin-bottom: 20px; }
.toggle-label {
  display: flex; align-items: center; gap: 10px; cursor: pointer;
  font-size: 14px; color: var(--muted); margin-bottom: 12px;
  user-select: none;
}
.toggle-label input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--copper); }
.recurring-options { padding: 14px; background: var(--surface2); border-radius: var(--radius); border: 1px solid var(--border); }

/* Summary panel */
.summary-panel { position: sticky; top: calc(var(--nav-h) + 20px); }
.summary-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border); font-size: 14px; }
.summary-item:last-child { border-bottom: none; }
.summary-item .label { color: var(--muted); }
.summary-item .val { font-weight: 600; }
.summary-empty { color: var(--muted); font-size: 13px; text-align: center; padding: 20px 0; }

/* My bookings */
.my-bookings { margin-top: 32px; }
.booking-row {
  padding: 14px 0; border-bottom: 1px solid var(--border);
  display: flex; align-items: center; gap: 14px;
}
.booking-row:last-child { border-bottom: none; }
.booking-color-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.booking-info { flex: 1; }
.booking-info strong { font-size: 14px; display: block; }
.booking-info span { font-size: 12px; color: var(--muted); }
.booking-status { font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; }
.booking-status.pending { color: var(--copper-lt); }
.booking-status.confirmed { color: var(--success); }
.booking-status.cancelled, .booking-status.rejected { color: var(--muted); }
.cancel-btn { font-size: 11px; color: var(--muted); border: 1px solid var(--border); background: none;
  padding: 4px 10px; border-radius: var(--radius); cursor: pointer; transition: all var(--transition); }
.cancel-btn:hover { color: var(--danger); border-color: rgba(192,57,43,0.35); }

@media (max-width: 860px) {
  .booking-layout { grid-template-columns: 1fr; }
  .summary-panel { position: static; }
  .slots-grid { grid-template-columns: repeat(3,1fr); }
}
</style>

<?php require APP_PATH . '/Views/partials/nav.php'; ?>

<div class="booking-layout">
  <!-- LEFT: Booking form -->
  <div>
    <div class="booking-card">
      <div class="booking-card-header">
        <h2>Időpontfoglalás</h2>
        <p>Válassz szolgáltatást, napot és időpontot.</p>
      </div>
      <div class="booking-card-body">

        <!-- 1. Szolgáltatás -->
        <p class="eyebrow" style="margin-bottom:12px">1. Szolgáltatás</p>
        <div class="service-list" id="serviceList">
          <?php foreach ($services as $svc): ?>
          <div class="service-option" data-id="<?= $svc['id'] ?>" data-duration="<?= $svc['duration'] ?>" onclick="selectService(this)">
            <div class="service-dot" style="background:<?= htmlspecialchars($svc['color']) ?>"></div>
            <div class="service-info">
              <strong><?= htmlspecialchars($svc['name']) ?></strong>
              <span><?= htmlspecialchars($svc['description'] ?? '') ?></span>
            </div>
            <div class="service-meta">
              <?= $svc['price'] ? number_format($svc['price'], 0, ',', ' ') . ' Ft' : 'Egyedi' ?><br>
              <span style="color:var(--muted);font-weight:400"><?= $svc['duration'] ?> perc</span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- 2. Nap -->
        <p class="eyebrow" style="margin-bottom:12px">2. Nap kiválasztása</p>
        <div class="calendar-nav">
          <button class="cal-nav-btn" onclick="changeMonth(-1)">‹</button>
          <span class="cal-month" id="calMonth"></span>
          <button class="cal-nav-btn" onclick="changeMonth(1)">›</button>
        </div>
        <div class="calendar-grid" id="calGrid"></div>

        <!-- 3. Időpont -->
        <p class="eyebrow" style="margin-bottom:12px">3. Időpont</p>
        <div id="slotsWrap">
          <p style="color:var(--muted);font-size:13px">Először válassz szolgáltatást és napot.</p>
        </div>

        <!-- 4. Ismétlődés -->
        <div class="recurring-wrap" id="recurringWrap" style="display:none">
          <div style="display:none">
          <p class="eyebrow" style="margin-bottom:12px">4. Ismétlődés (opcionális)</p>
          <label class="toggle-label">
            <input type="checkbox" id="recurringCheck" onchange="toggleRecurring()">
            Heti ismétlődő foglalás (max 3 hónap)
          </label>
          <div class="recurring-options" id="recurringOptions" style="display:none">
            <div class="form-group" style="margin-bottom:0">
              <label>Hány héten át ismétlődjön?</label>
              <input type="number" id="recurringWeeks" min="2" max="13" value="4"
                style="background:var(--surface);border:1px solid var(--border);color:var(--white);padding:8px 12px;border-radius:var(--radius);width:100%;outline:none;margin-top:8px">
            </div>
          </div>
          </div>
        </div>

        <!-- 5. Megjegyzés -->
        <div class="form-group" id="noteWrap" style="display:none">
          <label>Megjegyzés (opcionális)</label>
          <textarea id="bookingNote" rows="2" placeholder="Bármilyen kívánság..." style="resize:vertical"></textarea>
        </div>

        <div id="bookingAlert" class="alert" style="display:none"></div>

        <button class="btn btn-copper full-w" id="submitBtn" style="display:none" onclick="submitBooking()">
          Foglalási igény beadása
        </button>

      </div>
    </div>

    <!-- Saját foglalásaim -->
    <div class="booking-card my-bookings" style="margin-top:24px">
      <div class="booking-card-header">
        <h2>Foglalásaim</h2>
      </div>
      <div class="booking-card-body" id="myBookings">
        <?php if (empty($myBookings)): ?>
          <p style="color:var(--muted);font-size:13px">Még nincs foglalásod.</p>
        <?php else: ?>
          <?php
          $shownGroups = [];
          foreach ($myBookings as $b):
            $isGroup = $b['is_recurring'] && $b['recurring_group_id'];
            $groupKey = $isGroup ? 'g' . $b['recurring_group_id'] : null;
            if ($groupKey && in_array($groupKey, $shownGroups)) continue;
            if ($groupKey) $shownGroups[] = $groupKey;
          ?>
          <div class="booking-row">
            <div class="booking-color-dot" style="background:<?= htmlspecialchars($b['service_color']) ?>"></div>
            <div class="booking-info">
              <strong><?= htmlspecialchars($b['service_name']) ?></strong>
              <span>
                <?= date('Y. F j.', strtotime($b['booking_date'])) ?> <?= substr($b['booking_time'],0,5) ?>
                <?php if ($isGroup): ?> · Ismétlődő (<?= $b['recurring_group_id'] ?>. csoport)<?php endif; ?>
              </span>
            </div>
            <span class="booking-status <?= $b['status'] ?>">
              <?= ['pending'=>'Várakozik','confirmed'=>'Megerősítve','cancelled'=>'Lemondva','rejected'=>'Elutasítva'][$b['status']] ?? $b['status'] ?>
            </span>
            <?php if (in_array($b['status'], ['pending','confirmed'])): ?>
              <?php if ($isGroup): ?>
                <button class="cancel-btn" onclick="cancelGroup(<?= $b['recurring_group_id'] ?>)">Csoport lemondása</button>
              <?php else: ?>
                <button class="cancel-btn" onclick="cancelBooking(<?= $b['id'] ?>)">Lemondás</button>
              <?php endif; ?>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- RIGHT: Summary -->
  <div class="summary-panel">
    <div class="booking-card">
      <div class="booking-card-header">
        <h2>Összefoglaló</h2>
      </div>
      <div class="booking-card-body" id="summaryBody">
        <p class="summary-empty">Válassz szolgáltatást, napot és időpontot a foglaláshoz.</p>
      </div>
    </div>
  </div>
</div>

<script>
const MAX_ADVANCE = <?= (int)$config['max_advance_days'] ?>;
let selectedServiceId = null, selectedDuration = 60;
let selectedDate = null, selectedTime = null;
let calYear, calMonth;

function initCalendar() {
  const now = new Date();
  calYear = now.getFullYear();
  calMonth = now.getMonth();
  renderCalendar();
}

function changeMonth(dir) {
  calMonth += dir;
  if (calMonth < 0) { calMonth = 11; calYear--; }
  if (calMonth > 11) { calMonth = 0; calYear++; }
  renderCalendar();
}

function renderCalendar() {
  const days = ['H','K','Sz','Cs','P','Szo','V'];
  const grid = document.getElementById('calGrid');
  const monthEl = document.getElementById('calMonth');
  const now = new Date(); now.setHours(0,0,0,0);
  const maxDate = new Date(); maxDate.setDate(maxDate.getDate() + MAX_ADVANCE);

  const monthNames = ['Január','Február','Március','Április','Május','Június',
    'Július','Augusztus','Szeptember','Október','November','December'];
  monthEl.textContent = calYear + '. ' + monthNames[calMonth];

  let html = days.map(d => `<div class="cal-head">${d}</div>`).join('');

  const first = new Date(calYear, calMonth, 1);
  let startDay = first.getDay(); // 0=Sun
  startDay = startDay === 0 ? 6 : startDay - 1; // Mon=0

  for (let i = 0; i < startDay; i++) html += '<div class="cal-day empty"></div>';

  const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
  for (let d = 1; d <= daysInMonth; d++) {
    const date = new Date(calYear, calMonth, d);
    const dateStr = date.toISOString().split('T')[0];
    let cls = 'cal-day';
    if (date < now) cls += ' past';
    else if (date > maxDate) cls += ' blocked';
    else cls += ' available';
    if (date.toDateString() === now.toDateString()) cls += ' today';
    if (dateStr === selectedDate) cls += ' selected';
    html += `<div class="${cls}" onclick="selectDay('${dateStr}', this)">${d}</div>`;
  }
  grid.innerHTML = html;
}

function selectService(el) {
  document.querySelectorAll('.service-option').forEach(e => e.classList.remove('selected'));
  el.classList.add('selected');
  selectedServiceId = el.dataset.id;
  selectedDuration  = parseInt(el.dataset.duration);
  selectedTime = null;
  loadSlots();
  updateSummary();
}

function selectDay(dateStr, el) {
  if (el.classList.contains('past') || el.classList.contains('blocked') || el.classList.contains('empty')) return;
  selectedDate = dateStr;
  selectedTime = null;
  document.querySelectorAll('.cal-day').forEach(e => e.classList.remove('selected'));
  el.classList.add('selected');
  loadSlots();
  updateSummary();
}

async function loadSlots() {
  const wrap = document.getElementById('slotsWrap');
  if (!selectedServiceId || !selectedDate) {
    wrap.innerHTML = '<p style="color:var(--muted);font-size:13px">Válassz szolgáltatást és napot.</p>';
    return;
  }
  wrap.innerHTML = '<p style="color:var(--muted);font-size:13px">Betöltés...</p>';
  try {
    const res  = await fetch(`/api/slots?date=${selectedDate}&service_id=${selectedServiceId}`);
    const data = await res.json();
    if (!data.slots?.length) {
      wrap.innerHTML = `<p style="color:var(--muted);font-size:13px">${data.reason || 'Nincs elérhető időpont ezen a napon.'}</p>`;
      document.getElementById('recurringWrap').style.display = 'none';
      document.getElementById('noteWrap').style.display = 'none';
      document.getElementById('submitBtn').style.display = 'none';
      return;
    }
    wrap.innerHTML = '<div class="slots-grid">' + data.slots.map(s => {
      let cls = 'slot-btn';
      if (s.available) cls += ' available';
      else if (s.reason === 'taken') cls += ' taken';
      else cls += ' blocked';
      if (s.time_full === selectedTime || s.time === selectedTime?.substring(0,5)) cls += ' selected';
      const click = s.available ? `onclick="selectSlot('${s.time_full}', this)"` : '';
      return `<div class="${cls}" ${click}>${s.time}</div>`;
    }).join('') + '</div>';
  } catch {
    wrap.innerHTML = '<p style="color:var(--muted);font-size:13px">Hiba a betöltés közben.</p>';
  }
}

function selectSlot(timeFull, el) {
  selectedTime = timeFull;
  document.querySelectorAll('.slot-btn').forEach(e => e.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('recurringWrap').style.display = 'block';
  document.getElementById('noteWrap').style.display = 'block';
  document.getElementById('submitBtn').style.display = 'block';
  updateSummary();
}

function toggleRecurring() {
  const checked = document.getElementById('recurringCheck').checked;
  document.getElementById('recurringOptions').style.display = checked ? 'block' : 'none';
}

function updateSummary() {
  const body = document.getElementById('summaryBody');
  const sel = document.querySelector('.service-option.selected');
  if (!sel && !selectedDate && !selectedTime) {
    body.innerHTML = '<p class="summary-empty">Válassz szolgáltatást, napot és időpontot.</p>';
    return;
  }
  let html = '';
  if (sel) {
    const name  = sel.querySelector('strong')?.textContent || '';
    const price = sel.querySelector('.service-meta')?.firstChild?.textContent?.trim() || '';
    html += `<div class="summary-item"><span class="label">Szolgáltatás</span><span class="val">${name}</span></div>`;
    html += `<div class="summary-item"><span class="label">Időtartam</span><span class="val">${selectedDuration} perc</span></div>`;
    html += `<div class="summary-item"><span class="label">Ár</span><span class="val" style="color:var(--copper)">${price}</span></div>`;
  }
  if (selectedDate) {
    const d = new Date(selectedDate);
    const fmt = d.toLocaleDateString('hu-HU', {year:'numeric',month:'long',day:'numeric'});
    html += `<div class="summary-item"><span class="label">Nap</span><span class="val">${fmt}</span></div>`;
  }
  if (selectedTime) {
    html += `<div class="summary-item"><span class="label">Időpont</span><span class="val">${selectedTime.substring(0,5)}</span></div>`;
    html += `<div class="summary-item" style="border:none;padding-top:14px"><span style="font-size:12px;color:var(--muted)">Admin jóváhagyás után válik véglegessé.</span></div>`;
  }
  body.innerHTML = html || '<p class="summary-empty">Válassz a bal oldali panelen.</p>';
}

async function submitBooking() {
  const btn = document.getElementById('submitBtn');
  const alertEl = document.getElementById('bookingAlert');
  if (!selectedServiceId || !selectedDate || !selectedTime) {
    showAlert('error', 'Kérjük töltsd ki az összes mezőt.'); return;
  }
  btn.disabled = true; btn.textContent = 'Küldés...';
  alertEl.style.display = 'none';

  const isRecurring = document.getElementById('recurringCheck').checked;
  const weeks = isRecurring ? parseInt(document.getElementById('recurringWeeks').value) : 0;

  const body = new FormData();
  body.append('service_id', selectedServiceId);
  body.append('date', selectedDate);
  body.append('time', selectedTime);
  body.append('note', document.getElementById('bookingNote').value);
  body.append('recurring', weeks);

  try {
    const res  = await fetch('/api/foglalas', { method: 'POST', body });
    const data = await res.json();
    if (data.success) {
      showAlert('success', data.message || 'Foglalás sikeresen beadva!');
      selectedTime = null; selectedDate = null;
      setTimeout(() => location.reload(), 1800);
    } else {
      showAlert('error', data.message || 'Hiba történt.');
    }
  } catch {
    showAlert('error', 'Szerver hiba. Kérjük próbálja újra.');
  }
  btn.disabled = false; btn.textContent = 'Foglalási igény beadása';
}

function showAlert(type, msg) {
  const el = document.getElementById('bookingAlert');
  el.className = `alert alert-${type}`;
  el.textContent = msg;
  el.style.display = 'block';
}

async function cancelBooking(id) {
  if (!confirm('Biztosan lemondod ezt a foglalást?')) return;
  const body = new FormData(); body.append('id', id);
  const res = await fetch('/api/foglalas/lemondas', { method: 'POST', body });
  const data = await res.json();
  if (data.success) location.reload();
  else alert(data.message || 'Hiba történt.');
}

async function cancelGroup(groupId) {
  if (!confirm('Biztosan lemondod az összes jövőbeli ismétlődő foglalást ebből a csoportból?')) return;
  const body = new FormData(); body.append('group_id', groupId);
  const res = await fetch('/api/foglalas/csoport-lemondas', { method: 'POST', body });
  const data = await res.json();
  if (data.success) location.reload();
  else alert(data.message || 'Hiba történt.');
}

initCalendar();
updateSummary();
</script>

<script src="/js/main.js"></script>
</body>
</html>
