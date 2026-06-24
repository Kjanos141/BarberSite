// ---- TOKEN VALIDATION ----
const params     = new URLSearchParams(window.location.search);
const token      = params.get('token');
const tokenInput = document.getElementById('token');
const tokenError = document.getElementById('tokenError');
const formWrap   = document.getElementById('registerFormWrap');

if (!token) {
  tokenError.style.display = 'block';
  formWrap.style.display = 'none';
} else {
  fetch(`/token-ellenorzes?token=${encodeURIComponent(token)}`)
    .then(r => r.json())
    .then(data => {
      if (!data.valid) {
        tokenError.style.display = 'block';
        formWrap.style.display = 'none';
      } else {
        if (tokenInput) tokenInput.value = token;
        const emailInput = document.getElementById('regEmail');
        if (emailInput && data.email) emailInput.value = data.email;
      }
    })
    .catch(() => {
      tokenError.style.display = 'block';
      formWrap.style.display = 'none';
    });
}

// ---- PASSWORD TOGGLE ----
const togglePwReg = document.getElementById('togglePwReg');
const regPw       = document.getElementById('regPassword');
if (togglePwReg && regPw) {
  togglePwReg.addEventListener('click', () => {
    regPw.type = regPw.type === 'password' ? 'text' : 'password';
    togglePwReg.textContent = regPw.type === 'password' ? '👁' : '🙈';
  });
}

// ---- REGISTER ----
const registerBtn = document.getElementById('registerBtn');
if (registerBtn) {
  registerBtn.addEventListener('click', async () => {
    const pw1 = document.getElementById('regPassword').value;
    const pw2 = document.getElementById('regPassword2').value;

    if (pw1 !== pw2) { alert('A két jelszó nem egyezik meg.'); return; }

    registerBtn.textContent = 'Fiók létrehozása...';
    registerBtn.disabled = true;

    const body = new FormData();
    body.append('token',     tokenInput.value);
    body.append('name',      document.getElementById('regName').value.trim());
    body.append('email',     document.getElementById('regEmail').value.trim());
    body.append('password',  pw1);
    body.append('password2', pw2);

    try {
      const res  = await fetch('/regisztracio', { method: 'POST', body });
      const json = await res.json();
      if (json.success) {
        window.location.href = '/login?registered=1';
      } else {
        alert(json.message || 'Hiba történt. Kérjük próbálja újra.');
      }
    } catch {
      alert('Szerver hiba. Kérjük próbálja újra.');
    }

    registerBtn.textContent = 'Fiók létrehozása';
    registerBtn.disabled = false;
  });
}
