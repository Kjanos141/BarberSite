<?php
$title = 'Admin Dashboard — Bukta Zoltán EV';
$extraCss = ['/css/admin.css'];
require APP_PATH . '/Views/partials/head.php';
?>
<body class="admin-page">
<?php require APP_PATH . '/Views/partials/sidebar.php'; ?>

<main class="admin-main">
  <div class="admin-topbar">
    <button class="burger-btn" id="sidebarToggle">☰</button>
    <h1 class="admin-page-title">Áttekintés</h1>
  </div>
  <div class="admin-content">

    <!-- STAT CARDS -->
    <div class="stat-cards">
      <div class="stat-card">
        <p class="stat-label">Összes felhasználó</p>
        <span class="stat-big" id="statTotal">—</span>
      </div>
      <div class="stat-card">
        <p class="stat-label">Aktív felhasználó</p>
        <span class="stat-big copper" id="statActive">—</span>
      </div>
      <div class="stat-card">
        <p class="stat-label">Függő meghívó</p>
        <span class="stat-big" id="statPending">—</span>
      </div>
      <div class="stat-card">
        <p class="stat-label">Admin fiók</p>
        <span class="stat-big" id="statAdmins">—</span>
      </div>
    </div>

    <!-- RECENT USERS -->
    <div class="admin-card">
      <div class="card-header">
        <h3>Legutóbbi felhasználók</h3>
        <a href="/admin/felhasznalok" class="card-action">Összes megtekintése →</a>
      </div>
      <div class="table-wrap">
        <table class="data-table" id="recentUsersTable">
          <thead>
            <tr>
              <th>Név</th><th>E-mail</th><th>Szerep</th><th>Státusz</th><th>Regisztrált</th>
            </tr>
          </thead>
          <tbody><tr><td colspan="5" class="table-loading">Betöltés...</td></tr></tbody>
        </table>
      </div>
    </div>

    <!-- QUICK INVITE -->
    <div class="admin-card">
      <div class="card-header">
        <h3>Gyors meghívó küldés</h3>
      </div>
      <div class="inline-form">
        <div class="form-row">
          <div class="form-group">
            <label>E-mail cím</label>
            <input type="email" id="inviteEmail" placeholder="nev@email.hu" required>
          </div>
          <div class="form-group">
            <label>Szerepkör</label>
            <select id="inviteRole">
              <option value="user">Felhasználó</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="form-group" style="justify-content:flex-end">
            <button type="button" id="quickInviteBtn" class="btn btn-copper">Meghívó küldése</button>
          </div>
        </div>
        <div id="inviteMsg" class="alert" style="display:none;margin-top:12px"></div>
      </div>
    </div>

  </div>
</main>

<script src="/js/admin.js"></script>
</body>
</html>
