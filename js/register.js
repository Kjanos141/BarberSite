// ---- INVITE TOKEN VALIDATION ----
const params = new URLSearchParams(window.location.search);
const token = params.get('token');
const tokenInput = document.getElementById('token');
const tokenError = document.getElementById('tokenError');
const registerForm = document.getElementById('registerForm');

if (!token) {
  tokenError.style.display = 'block';
  registerForm.style.display = 'none';
} else {
  // Validate token with server
  fetch(`php/validate_token.php?token=${encodeURIComponent(token)}`)
    .then(r => r.json())
    .then(data => {
      if (!data.valid) {
        tokenError.style.display = 'block';
        registerForm.style.display = 'none';
      } else {
        tokenInput.value = token;
        const emailInput = document.getElementById('regEmail');
        if (emailInput && data.email) emailInput.value = data.email;
      }
    })
    .catch(() => {
      tokenError.style.display = 'block';
      registerForm.style.display = 'none';
    });
}

// ---- PASSWORD TOGGLE ----
const togglePwReg = document.getElementById('togglePwReg');
const regPw = document.getElementById('regPassword');
if (togglePwReg && regPw) {
  togglePwReg.addEventListener('click', () => {
    regPw.type = regPw.type === 'password' ? 'text' : 'password';
    togglePwReg.textContent = regPw.type === 'password' ? '👁' : '🙈';
  });
}

// ---- REGISTER FORM ----
if (registerForm) {
  registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const pw1 = document.getElementById('regPassword').value;
    const pw2 = document.getElementById('regPassword2').value;
    if (pw1 !== pw2) {
      alert('A két jelszó nem egyezik meg.');
      return;
    }
    const btn = registerForm.querySelector('button[type="submit"]');
    btn.textContent = 'Fiók létrehozása...';
    btn.disabled = true;

    const data = new FormData(registerForm);
    try {
      const res = await fetch('php/register.php', { method: 'POST', body: data });
      const json = await res.json();
      if (json.success) {
        window.location.href = 'login.html?registered=1';
      } else {
        alert(json.message || 'Hiba történt. Kérjük próbálja újra.');
        btn.textContent = 'Fiók létrehozása';
        btn.disabled = false;
      }
    } catch {
      alert('Szerver hiba. Kérjük próbálja újra.');
      btn.textContent = 'Fiók létrehozása';
      btn.disabled = false;
    }
  });
}
