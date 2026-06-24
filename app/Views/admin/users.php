<?php
$title = 'Felhasználók — Bukta Zoltán EV Admin';
$extraCss = ['/css/admin.css'];
require APP_PATH . '/Views/partials/head.php';
?>
<body class="admin-page">
<?php require APP_PATH . '/Views/partials/sidebar.php'; ?>

<main class="admin-main">
  <div class="admin-topbar">
    <button class="burger-btn" id="sidebarToggle">☰</button>
    <h1 class="admin-page-title">Felhasználók kezelése</h1>
    <a href="/admin/meghivo" class="btn btn-copper topbar-action">+ Meghívó küldése</a>
  </div>
  <div class="admin-content">
    <div class="admin-card">
      <div class="card-header">
        <h3>Összes felhasználó</h3>
        <div class="filter-row">
          <input type="text" id="searchUsers" placeholder="Keresés..." class="search-input">
          <select id="filterRole" class="filter-select">
            <option value="">Minden szerep</option>
            <option value="admin">Admin</option>
            <option value="user">Felhasználó</option>
          </select>
          <select id="filterStatus" class="filter-select">
            <option value="">Minden státusz</option>
            <option value="active">Aktív</option>
            <option value="inactive">Inaktív</option>
          </select>
        </div>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Név</th><th>E-mail</th><th>Szerep</th><th>Státusz</th><th>Regisztrált</th><th>Műveletek</th>
            </tr>
          </thead>
          <tbody id="usersTbody">
            <tr><td colspan="6" class="table-loading">Betöltés...</td></tr>
          </tbody>
        </table>
      </div>
      <div class="table-footer">
        <span id="tableCount" class="table-count"></span>
      </div>
    </div>
  </div>
</main>

<!-- CONFIRM MODAL -->
<div class="modal-overlay" id="confirmOverlay" style="display:none">
  <div class="modal modal-sm">
    <h3 id="confirmTitle">Megerősítés</h3>
    <p id="confirmMsg"></p>
    <div class="modal-actions">
      <button class="btn btn-ghost" id="confirmCancel">Mégsem</button>
      <button class="btn btn-danger" id="confirmOk">Megerősítés</button>
    </div>
  </div>
</div>

<script src="/js/admin.js"></script>
<script src="/js/users.js"></script>
</body>
</html>
