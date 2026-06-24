// ---- PASSWORD TOGGLE ----
const togglePw = document.getElementById('togglePw');
const pwInput = document.getElementById('password');
if (togglePw && pwInput) {
  togglePw.addEventListener('click', () => {
    pwInput.type = pwInput.type === 'password' ? 'text' : 'password';
    togglePw.textContent = pwInput.type === 'password' ? '👁' : '🙈';
  });
}

// ---- LOGIN FORM ----
const loginForm = document.getElementById('loginForm');
const loginError = document.getElementById('loginError');
if (loginForm) {
  loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = loginForm.querySelector('button[type="submit"]');
    btn.textContent = 'Belépés...';
    btn.disabled = true;

    const data = new FormData(loginForm);
    try {
      const res = await fetch('php/login.php', { method: 'POST', body: data });
      const json = await res.json();
      if (json.success) {
        window.location.href = json.redirect || 'admin.html';
      } else {
        loginError.style.display = 'block';
        loginError.textContent = json.message || 'Hibás e-mail cím vagy jelszó.';
        btn.textContent = 'Belépés';
        btn.disabled = false;
      }
    } catch {
      loginError.style.display = 'block';
      loginError.textContent = 'Szerver hiba. Kérjük próbálja újra.';
      btn.textContent = 'Belépés';
      btn.disabled = false;
    }
  });
}
