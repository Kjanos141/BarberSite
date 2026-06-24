<?php
alert("asd");exit();
$title = 'Bejelentkezés — Bukta Zoltán EV';
require APP_PATH . '/Views/partials/head.php';
?>
<body class="auth-page">
<div class="auth-layout">
  <div class="auth-panel">
    <a href="/" class="auth-back">← Vissza a főoldalra</a>
    <div class="auth-brand">
      <div class="logo-monogram" style="width:52px;height:52px;font-size:22px">BZ</div>
      <div class="logo-text-wrap" style="align-items:flex-start">
        <span class="logo-name" style="font-size:18px">Bukta Zoltán</span>
        <span class="logo-sub">Egyéni Vállalkozó</span>
      </div>
      <p class="auth-tagline" style="margin-top:20px">Ügyfélkapu belépés</p>
    </div>
  </div>
  <div class="auth-form-panel">
    <div class="auth-form-wrap">
      <p class="form-eyebrow">Üdvözöljük</p>
      <h2 class="form-title">Bejelentkezés</h2>
      <div class="copper-rule"></div>

      <div id="loginError" class="alert alert-error" style="display:none"></div>

      <div class="form-group">
        <label for="email">E-mail cím</label>
        <input type="email" id="email" name="email" placeholder="nev@email.hu" required autocomplete="email">
      </div>
      <div class="form-group">
        <label for="password">Jelszó</label>
        <div class="input-wrap">
          <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
          <button type="button" class="toggle-pw" id="togglePw">👁</button>
        </div>
      </div>
      <button type="button" id="loginBtn" class="btn btn-copper full-w">Belépés</button>

      <p class="auth-note">Fiókot kizárólag meghívó alapján lehet létrehozni.<br>Ha meghívót kaptál, ellenőrizd az e-mailed.</p>
    </div>
  </div>
</div>
<script src="/js/auth.js"></script>
</body>
</html>
