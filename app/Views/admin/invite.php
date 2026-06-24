<?php
$title = 'Meghívó küldés — Bukta Zoltán EV Admin';
$extraCss = ['/css/admin.css'];
require APP_PATH . '/Views/partials/head.php';
?>
<body class="admin-page">
<?php require APP_PATH . '/Views/partials/sidebar.php'; ?>

<main class="admin-main">
  <div class="admin-topbar">
    <button class="burger-btn" id="sidebarToggle">☰</button>
    <h1 class="admin-page-title">Meghívó küldése</h1>
  </div>
  <div class="admin-content">

    <div class="admin-card invite-card">
      <div class="invite-icon">✉</div>
      <h3>Új felhasználó meghívása</h3>
      <p class="invite-desc">Az érintett személy e-mailben kap egy egyedi meghívó linket, amelyen keresztül létrehozhatja fiókját. A link 48 óráig érvényes.</p>
      <div class="copper-rule center"></div>
      <div class="invite-form">
        <div class="form-group">
          <label>Teljes név</label>
          <input type="text" id="invNameFull" placeholder="Kiss János" required>
        </div>
        <div class="form-group">
          <label>E-mail cím</label>
          <input type="email" id="invEmailFull" placeholder="nev@email.hu" required>
        </div>
        <div class="form-group">
          <label>Szerepkör</label>
          <select id="invRoleFull">
            <option value="user">Felhasználó</option>
            <option value="admin">Adminisztrátor</option>
          </select>
        </div>
        <div class="form-group">
          <label>Megjegyzés (opcionális)</label>
          <textarea id="invNote" rows="3" placeholder="Személyes üzenet a meghívóhoz..."></textarea>
        </div>
        <button type="button" id="sendInviteBtn" class="btn btn-copper full-w">Meghívó elküldése</button>
        <div id="inviteResult" class="alert" style="display:none;margin-top:14px"></div>
      </div>
    </div>

    <!-- PENDING INVITES -->
    <div class="admin-card">
      <div class="card-header">
        <h3>Függő meghívók</h3>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>E-mail</th><th>Szerep</th><th>Elküldve</th><th>Lejár</th><th>Műveletek</th></tr>
          </thead>
          <tbody id="pendingInvites">
            <tr><td colspan="5" class="table-loading">Betöltés...</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>

<script src="/js/admin.js"></script>
<script src="/js/invite.js"></script>
</body>
</html>
