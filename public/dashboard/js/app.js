const DASHBOARD_HOME = 'consultas.php';

/* Guarda el token en una cookie para que el server lo valide al cargar el dashboard */
function guardarCookie(token) {
  const d = new Date();
  d.setTime(d.getTime() + 24 * 60 * 60 * 1000);
  document.cookie = 'tdt_token=' + encodeURIComponent(token) + '; path=/; expires=' + d.toUTCString() + '; SameSite=Lax';
}

/* Maneja el submit del formulario de login.
   Envia credenciales a la API, guarda el token y redirige a Consultas */
async function handleLogin(e) {
  e.preventDefault();
  const el = document.getElementById('login-error');
  const btn = e.target.querySelector('button[type="submit"]');
  const email = e.target.email.value.trim();
  const pass = e.target.contrasena.value.trim();
  debugger;
  if (!email || !pass) {
    if (el) { el.textContent = 'Ingrese email y contraseña'; el.classList.add('visible'); }
    return;
  }
  btn.disabled = true;
  btn.textContent = 'Ingresando...';
  try {
    const d = await api('POST', '/usuarios/login', { email, contrasena: pass });
    localStorage.setItem(TOKEN_KEY, d.token);
    guardarCookie(d.token);
    const p = JSON.parse(atob(d.token.split('.')[1]));
    localStorage.setItem(USER_KEY, JSON.stringify({
      id: p.sub, email: p.email, nombre: d.nombre || email.split('@')[0], rol: d.rol || 'admin'
    }));
    window.location.href = DASHBOARD_HOME;
  } catch (err) {

    if (el) { el.textContent = err.message; el.classList.add('visible'); }
    btn.disabled = false;
    btn.textContent = 'Ingresar';
  }
}

(function redirectIfAlreadyLoggedIn() {
  const token = getToken();
  if (!token) return;

  try {
    const payload = JSON.parse(atob(token.split('.')[1]));
    if (payload.exp * 1000 > Date.now()) {
      window.location.replace(DASHBOARD_HOME);
    }
  } catch (e) {
    // Si el token no se puede leer, se deja mostrar el login.
  }
})();
