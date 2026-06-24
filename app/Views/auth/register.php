<?php
$title = 'Regisztráció — Bukta Zoltán EV';
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
      <p class="auth-tagline" style="margin-top:20px">Meghívó alapú regisztráció</p>
    </div>
  </div>
  <div class="auth-form-panel">
    <div class="auth-form-wrap">
      <p class="form-eyebrow">Üdvözöljük</p>
      <h2 class="form-title">Fiók létrehozása</h2>
      <div class="copper-rule"></div>

      <div id="tokenError" class="alert alert-error" style="display:none">
        Érvénytelen vagy lejárt meghívó link. Kérjen új meghívót az adminisztrátortól.
      </div>

      <div id="registerFormWrap">
        <input type="hidden" id="token" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
        <div class="form-group">
          <label for="regName">Teljes név</label>
          <input type="text" id="regName" placeholder="Kiss János" required>
        </div>
        <div class="form-group">
          <label for="regEmail">E-mail cím</label>
          <input type="email" id="regEmail" placeholder="nev@email.hu" required readonly>
        </div>
        <div class="form-group">
          <label for="regPassword">Jelszó</label>
          <div class="input-wrap">
            <input type="password" id="regPassword" placeholder="Min. 8 karakter" required minlength="8">
            <button type="button" class="toggle-pw" id="togglePwReg">👁</button>
          </div>
        </div>
        <div class="form-group">
          <label for="regPassword2">Jelszó megerősítése</label>
          <input type="password" id="regPassword2" placeholder="••••••••" required>
        </div>
        <button type="button" id="registerBtn" class="btn btn-copper full-w">Fiók létrehozása</button>
      </div>

      <p class="auth-note">Ha problémád van a regisztrációval,<br>vedd fel a kapcsolatot az adminisztrátorral.</p>
    </div>
  </div>
</div>
<script src="/js/register.js"></script>
</body>
</html>
