<?php
$title = 'Vezérlőpult — Bukta Zoltán EV';
require APP_PATH . '/Views/partials/head.php';
?>

<?php require APP_PATH . '/Views/partials/nav.php'; ?>

<section class="hero" style="min-height:100vh; display:flex; align-items:center;">
  <div class="hero-bg"></div>
  <div class="hero-content" style="width:100%;">
    <p class="hero-kicker">Vezérlőpult</p>
    <h1 class="hero-title" style="font-size:clamp(32px,4vw,56px);">
      Üdvözöljük,<br><em><?= htmlspecialchars($user['name'] ?? 'Felhasználó') ?>!</em>
    </h1>
    <p class="hero-sub">Sikeresen bejelentkezett a Bukta Zoltán EV rendszerébe.</p>
    <div class="hero-actions">
      <a href="/foglalas" class="btn btn-copper">Időpontfoglalás</a>
      <a href="/#kapcsolat" class="btn btn-copper">Kapcsolatfelvétel</a>
      <a href="/kijelentkezes" class="btn btn-ghost">Kilépés</a>
    </div>
  </div>
</section>

<script src="/js/main.js"></script>
</body>
</html>