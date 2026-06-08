/* Maneja el submit del formulario de login.
   Valida campos, envia credenciales a la API, guarda el token y redirige al panel */
async function handleLogin(e) {
  e.preventDefault();
  const el = document.getElementById('login-error');
  const btn = e.target.querySelector('button[type="submit"]');
  const email = e.target.email.value.trim();
  const pass = e.target.contrasena.value.trim();
  if (!email || !pass) {
    if (el) { el.textContent = 'Ingrese email y contrasena'; el.classList.add('visible'); }
    return;
  }
  btn.disabled = true;
  btn.textContent = 'Ingresando...';
  try {
    const d = await api('POST', '/usuarios/login', { email, contrasena: pass });
    localStorage.setItem(TOKEN_KEY, d.token);
    const p = JSON.parse(atob(d.token.split('.')[1]));
    localStorage.setItem(USER_KEY, JSON.stringify({
      id: p.sub, email: p.email, nombre: d.nombre || email.split('@')[0], rol: d.rol || 'admin'
    }));
    window.location.href = 'admin.html';
  } catch (err) {
    if (el) { el.textContent = err.message; el.classList.add('visible'); }
    btn.disabled = false;
    btn.textContent = 'Ingresar';
  }
}
