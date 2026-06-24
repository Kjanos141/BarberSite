<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$adminUser = $user ?? \App\Core\Auth::user();
$initial = strtoupper(substr($adminUser['name'] ?? 'A', 0, 1));
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="logo-monogram">BZ</div>
    <div class="logo-text-wrap">
      <span class="logo-name" style="font-size:13px">Bukta Zoltán</span>
      <span class="logo-sub">EV Admin</span>
    </div>
  </div>
  <nav class="sidebar-nav">
    <a href="/admin" class="sidebar-link <?= $currentPath === '/admin' ? 'active' : '' ?>">
      <span class="nav-icon">◉</span> Áttekintés
    </a>
    <a href="/admin/felhasznalok" class="sidebar-link <?= $currentPath === '/admin/felhasznalok' ? 'active' : '' ?>">
      <span class="nav-icon">◎</span> Felhasználók
    </a>
    <a href="/admin/meghivo" class="sidebar-link <?= $currentPath === '/admin/meghivo' ? 'active' : '' ?>">
      <span class="nav-icon">✉</span> Meghívó küldés
    </a>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="user-avatar"><?= $initial ?></div>
      <div class="user-info">
        <span class="user-name"><?= htmlspecialchars($adminUser['name'] ?? 'Admin') ?></span>
        <span class="user-role">Adminisztrátor</span>
      </div>
    </div>
    <a href="/kijelentkezes" class="logout-btn">Kilépés</a>
  </div>
</aside>
