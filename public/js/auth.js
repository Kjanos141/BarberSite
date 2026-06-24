// ---- PASSWORD TOGGLE ----
const togglePw = document.getElementById('togglePw');
const pwInput  = document.getElementById('password');
if (togglePw && pwInput) {
  togglePw.addEventListener('click', () => {
    pwInput.type = pwInput.type === 'password' ? 'text' : 'password';
    togglePw.textContent = pwInput.type === 'password' ? '👁' : '🙈';
  });
}

// ---- LOGIN ----
const loginBtn   = document.getElementById('loginBtn');
const loginError = document.getElementById('loginError');

if (loginBtn) {
  loginBtn.addEventListener('click', async () => {
    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    loginBtn.textContent = 'Belépés...';
    loginBtn.disabled = true;
    loginError.style.display = 'none';

    try {
      const body = new FormData();
      body.append('email', email);
      body.append('password', password);

      const res  = await fetch('/login', { method: 'POST', body });
      const json = await res.json();

      if (json.success) {
        window.location.href = json.redirect || '/admin';
      } else {
        loginError.style.display = 'block';
        loginError.textContent = json.message || 'Hibás e-mail cím vagy jelszó.';
      }
    } catch {
      loginError.style.display = 'block';
      loginError.textContent = 'Szerver hiba. Kérjük próbálja újra.';
    }

    loginBtn.textContent = 'Belépés';
    loginBtn.disabled = false;
  });

  // Enter key support
  [document.getElementById('email'), document.getElementById('password')].forEach(el => {
    el?.addEventListener('keydown', e => { if (e.key === 'Enter') loginBtn.click(); });
  });
}
