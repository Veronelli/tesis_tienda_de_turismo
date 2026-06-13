/* Calcula la URL base de la API recortando el prefijo /dashboard/ de la URL actual.
   Ej: /tienda/.../dashboard/login.html → /tienda/.../api */
const API_BASE = (() => {
  const p = window.location.pathname;
  const i = p.indexOf('/dashboard/');
  return p.substring(0, i) + '/api';
})();

const TOKEN_KEY = 'tdt_token';
const USER_KEY = 'tdt_user';

/* Recupera el JWT del localStorage */
function getToken() { return localStorage.getItem(TOKEN_KEY); }

/* Recupera y parsea los datos del usuario guardados al iniciar sesion */
function getUser() { try { return JSON.parse(localStorage.getItem(USER_KEY)); } catch { return null; } }

/* Elimina token (cookie + localStorage) y datos del usuario, redirige al login */
function logout() {
  localStorage.removeItem(TOKEN_KEY);
  localStorage.removeItem(USER_KEY);
  document.cookie = 'tdt_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC; SameSite=Lax';
  window.location.href = 'login.html';
}

/* Cliente HTTP generico que incluye el JWT en cada peticion.
   method: GET/POST/PUT, path: ej. /usuarios/login, body: objeto opcional.
   Si la API responde 401 (token invalido) cierra la sesion automaticamente */
async function api(method, path, body) {
  const h = { 'Content-Type': 'application/json' };
  const t = getToken();
  if (t) h['Authorization'] = 'Bearer ' + t;
  const opts = { method, headers: h };
  if (body) opts.body = JSON.stringify(body);
  try {
    const r = await fetch(API_BASE + path, opts);
    const d = await r.json();
    if (!r.ok) {
      if (r.status === 401 && path !== '/usuarios/login') logout();
      throw new Error(d.error || 'Error ' + r.status);
    }
    return d;
  } catch (e) {
    if (e.name === 'TypeError') throw new Error('No se puede conectar con el servidor');
    throw e;
  }
}
