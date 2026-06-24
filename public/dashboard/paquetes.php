<?php

$title = 'Paquetes';
$currentPage = 'paquetes';
$showFab = true;
$fabAction = 'abrirModalCrear()';

require_once __DIR__ . '/components/layout-start.php';
require_once __DIR__ . '/components/page-header.php';
?>

<div class="card">
  <div class="card-header">
    <h3>Listado de Paquetes</h3>
    <span class="total-badge" id="totalPaquetes"><strong>0</strong> paquetes</span>
  </div>
  <div class="card-body">
    <!-- Filters -->
    <div class="search-row" style="flex-wrap:wrap;">
      <input type="text" id="filtroNombre" class="form-control search-input" placeholder="Nombre del paquete..." style="max-width:200px;" onkeydown="if(event.key==='Enter')aplicarFiltros()">
      <select id="filtroMesPartida" class="form-control search-input" style="max-width:150px;">
        <option value="">Mes partida</option>
        <option value="1">Enero</option>
        <option value="2">Febrero</option>
        <option value="3">Marzo</option>
        <option value="4">Abril</option>
        <option value="5">Mayo</option>
        <option value="6">Junio</option>
        <option value="7">Julio</option>
        <option value="8">Agosto</option>
        <option value="9">Septiembre</option>
        <option value="10">Octubre</option>
        <option value="11">Noviembre</option>
        <option value="12">Diciembre</option>
      </select>
      <select id="filtroDestino" class="form-control search-input" style="max-width:200px;">
        <option value="">Todos los destinos</option>
      </select>
      <select id="filtroOrdenPrecio" class="form-control search-input" style="max-width:150px;">
        <option value="">Sin orden</option>
        <option value="asc">Precio ↑</option>
        <option value="desc">Precio ↓</option>
      </select>
      <button class="btn btn-primary btn-search" type="button" onclick="aplicarFiltros()">Buscar</button>
      <button class="btn btn-secondary btn-search" type="button" onclick="limpiarFiltros()">Limpiar</button>
    </div>

    <!-- Loading -->
    <div class="loading" id="loadingPaquetes">
      <div class="spinner"></div>
      Cargando paquetes...
    </div>

    <!-- List -->
    <div id="listaPaquetes" style="display:none;">
      <div class="pkg-grid" id="paquetesGrid"></div>
      <div class="empty-state" id="emptyPaquetes" style="display:none;">No hay paquetes registrados.</div>
    </div>
  </div>
</div>

<!-- Modal crear/editar paquete -->
<div class="modal-overlay" id="modalPaquete">
  <div class="modal" style="max-width:620px;">
    <div class="modal-header">
      <h3 id="modalPaqueteTitulo">Agregar Paquete</h3>
      <button class="modal-close" type="button" onclick="cerrarModal()" aria-label="Cerrar">&times;</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="paqueteId">
      <div class="form-group">
        <label for="paqueteNombre">Nombre del paquete *</label>
        <input type="text" id="paqueteNombre" class="form-control" placeholder="Ej: Viaje a Cancún" required maxlength="200">
      </div>
      <div class="form-group">
        <label for="paqueteDescripcion">Descripción</label>
        <textarea id="paqueteDescripcion" class="form-control" placeholder="Descripción del paquete..." maxlength="5000"></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="paqueteFechaPartida">Fecha de partida *</label>
          <input type="date" id="paqueteFechaPartida" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="paqueteFechaVuelta">Fecha de vuelta</label>
          <input type="date" id="paqueteFechaVuelta" class="form-control">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="paquetePrecio">Precio *</label>
          <input type="number" id="paquetePrecio" class="form-control" placeholder="Ej: 1250.00" step="0.01" min="0" required>
        </div>
        <div class="form-group">
          <label for="paqueteDisponible">Disponible</label>
          <div style="display:flex;align-items:center;height:38px;">
            <input type="checkbox" id="paqueteDisponible" checked style="accent-color:var(--accent);width:18px;height:18px;">
          </div>
        </div>
      </div>
      <div class="form-group">
        <label for="paqueteHoteles">Hoteles asociados *</label>
        <input type="text" id="buscarHoteles" class="form-control" placeholder="Buscar hoteles por nombre, ubicación o destino..." style="margin-bottom:6px;font-size:0.82rem;" oninput="filtrarHoteles(this.value)">
        <select id="paqueteHoteles" class="form-control" multiple style="height:auto;min-height:100px;appearance:auto;background-image:none;padding-right:12px;">
          <option value="">Cargando hoteles...</option>
        </select>
        <small style="color:var(--text-muted);font-size:0.72rem;">Ctrl+click para seleccionar múltiples hoteles</small>
      </div>
      <div class="form-group">
        <label>Imagen principal (opcional)</label>
        <div class="image-upload-row">
          <div class="image-upload">
            <div class="preview" id="previewImagen" onclick="document.getElementById('imagenInput').click()" style="cursor:pointer;position:relative;">
              <span>+</span>
            </div>
            <input type="file" id="imagenInput" accept="image/jpeg,image/png,image/webp" onchange="previewImagen(this)">
            <label class="upload-label" for="imagenInput">Seleccionar imagen</label>
            <button class="btn btn-ghost btn-sm" type="button" id="btnQuitarImagen" onclick="quitarImagen()" style="display:none;margin-top:6px;">Quitar imagen</button>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label>Imagen secundaria (opcional)</label>
        <div class="image-upload-row">
          <div class="image-upload">
            <div class="preview" id="previewImagenSecundaria" onclick="document.getElementById('imagenSecundariaInput').click()" style="cursor:pointer;position:relative;">
              <span>+</span>
            </div>
            <input type="file" id="imagenSecundariaInput" accept="image/jpeg,image/png,image/webp" onchange="previewImagenSecundaria(this)">
            <label class="upload-label" for="imagenSecundariaInput">Seleccionar imagen</label>
            <button class="btn btn-ghost btn-sm" type="button" id="btnQuitarImagenSecundaria" onclick="quitarImagenSecundaria()" style="display:none;margin-top:6px;">Quitar imagen secundaria</button>
          </div>
        </div>
      </div>
      <div id="errorValidacion" style="color:var(--status-cancelada);font-size:0.83rem;margin-bottom:12px;display:none;"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-danger" type="button" id="btnEliminarPaquete" onclick="eliminarPaquete()" style="display:none;margin-right:auto;">Eliminar</button>
      <button class="btn btn-secondary" type="button" onclick="cerrarModal()">Cancelar</button>
      <button class="btn btn-primary" type="button" id="btnGuardarPaquete" onclick="guardarPaquete()">Guardar</button>
    </div>
  </div>
</div>

<style>
.pkg-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 14px;
}
.pkg-card {
  background: var(--card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius);
  overflow: hidden;
  transition: box-shadow 0.15s;
}
.pkg-card:hover { box-shadow: var(--shadow-md); }
.pkg-card__img {
  width: 100%;
  height: 160px;
  background: var(--bg);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  font-size: 0.75rem;
  overflow: hidden;
}
.pkg-card__img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.pkg-card__img .placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  color: var(--text-muted);
  opacity: 0.5;
}
.pkg-card__body { padding: 14px 16px; }
.pkg-card__name { font-weight: 600; font-size: 0.92rem; color: var(--text); }
.pkg-card__desc { font-size: 0.78rem; color: var(--text-muted); margin-top: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.pkg-card__meta { display: flex; flex-wrap: wrap; gap: 6px 16px; margin-top: 8px; font-size: 0.78rem; color: var(--text-muted); }
.pkg-card__meta span { display: inline-flex; align-items: center; gap: 4px; }
.pkg-card__footer { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--border-light); }
.pkg-card__price { font-size: 1.05rem; font-weight: 700; color: var(--accent); }
.badge-available { font-size: 0.7rem; font-weight: 600; padding: 3px 10px; border-radius: 100px; }
.badge-available.si { background: rgba(34,197,94,0.12); color: #166534; }
.badge-available.no { background: rgba(239,68,68,0.12); color: #991b1b; }
.pkg-card__actions { display: flex; gap: 6px; }
.btn-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: 1px solid var(--border);
  border-radius: 6px;
  background: var(--card);
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.15s;
}
.btn-icon:hover {
  background: var(--bg);
  color: var(--accent);
  border-color: var(--accent);
}

.btn-danger { background: #dc2626; color: #fff; }
.btn-danger:hover { background: #b91c1c; }

/* Preview de imagen: que se adapte a la imagen sin dejar bordes vacios */
#previewImagen,
#previewImagenSecundaria {
  overflow: hidden;
}
#previewImagen:not(.sin-imagen),
#previewImagenSecundaria:not(.sin-imagen) {
  aspect-ratio: auto;
  border: 1px solid var(--border);
  background: transparent;
  padding: 4px;
}
#previewImagen img,
#previewImagenSecundaria img {
  display: block;
  width: 100%;
  height: auto;
  border-radius: 4px;
}
#previewImagen.sin-imagen,
#previewImagenSecundaria.sin-imagen {
  aspect-ratio: 16/9;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg);
  border: 2px dashed var(--border);
  font-size: 2rem;
  color: var(--text-muted);
}
</style>

<script>
let paquetesData = [];
let hotelesData = [];
let destinosData = [];
let editandoId = null;

// ----- api(), getToken() and logout() come from auth.js (included via layout-end.php) -----

// Sends a multipart/form-data request with Authorization header.
// Does NOT set Content-Type — the browser auto-generates it with the boundary.
async function enviarFormData(method, path, formData) {
  const token = getToken();
  if (!token) {
    mostrarToast('Sesión expirada. Volvé a iniciar sesión.', 'error');
    setTimeout(logout, 2000);
    throw new Error('Token no disponible');
  }
  let r;
  try {
    r = await fetch(API_BASE + path, {
      method,
      headers: { 'Authorization': 'Bearer ' + token },
      body: formData,
      credentials: 'same-origin'
    });
  } catch (e) {
    if (e.name === 'TypeError') throw new Error('No se puede conectar con el servidor');
    throw e;
  }
  const d = await r.json();
  if (!r.ok) {
    if (r.status === 401) logout();
    throw new Error(d.error || 'Error ' + r.status);
  }
  return d;
}

document.addEventListener('DOMContentLoaded', () => {
  cargarDestinos();
  cargarPaquetes();
});

// ----- Load data -----

async function cargarPaquetes() {
  document.getElementById('loadingPaquetes').style.display = 'flex';
  document.getElementById('listaPaquetes').style.display = 'none';
  try {
    const params = new URLSearchParams();
    const nombre = document.getElementById('filtroNombre').value.trim();
    const mes = document.getElementById('filtroMesPartida').value;
    const destino = document.getElementById('filtroDestino').value;
    const orden = document.getElementById('filtroOrdenPrecio').value;
    if (nombre) params.set('nombre', nombre);
    if (mes) params.set('mes_partida', mes);
    if (destino) params.set('destino_id', destino);
    if (orden) params.set('orden_precio', orden);
    const qs = params.toString();
    paquetesData = await api('GET', '/paquetes' + (qs ? '?' + qs : ''));
    renderizarPaquetes(paquetesData);
    document.getElementById('loadingPaquetes').style.display = 'none';
    document.getElementById('listaPaquetes').style.display = 'block';
  } catch (e) {
    document.getElementById('loadingPaquetes').innerHTML = '<p class="error-message">Error al cargar paquetes: ' + escapeHtml(e.message) + '</p>';
    mostrarToast(e.message, 'error');
  }
}

async function cargarDestinos() {
  try {
    destinosData = await api('GET', '/destinos');
    const select = document.getElementById('filtroDestino');
    const currentValue = select.value;
    select.innerHTML = '<option value="">Todos los destinos</option>';
    destinosData.forEach(d => {
      const opt = document.createElement('option');
      opt.value = d.id;
      const label = d.ciudad + (d.estado_provincia ? ', ' + d.estado_provincia : '') + ' - ' + d.pais;
      opt.textContent = label;
      select.appendChild(opt);
    });
    if (currentValue) select.value = currentValue;
  } catch (e) {
    mostrarToast('Error al cargar destinos: ' + e.message, 'error');
  }
}

async function cargarHoteles() {
  try {
    hotelesData = await api('GET', '/hoteles');
    const select = document.getElementById('paqueteHoteles');
    const selectedValues = Array.from(select.selectedOptions).map(o => o.value);
    select.innerHTML = '';
    if (hotelesData.length === 0) {
      const opt = document.createElement('option');
      opt.value = '';
      opt.textContent = 'No hay hoteles disponibles';
      opt.disabled = true;
      select.appendChild(opt);
    } else {
      hotelesData.forEach(h => {
        const opt = document.createElement('option');
        opt.value = h.id;
        const destinoText = h.destino ? h.destino.ciudad + ', ' + h.destino.pais : '';
        opt.textContent = h.nombre + (destinoText ? ' (' + destinoText + ')' : '');
        select.appendChild(opt);
      });
      selectedValues.forEach(v => {
        const opt = Array.from(select.options).find(o => o.value === v);
        if (opt) opt.selected = true;
      });
    }
  } catch (e) {
    mostrarToast('Error al cargar hoteles: ' + e.message, 'error');
  }
}

function formatearDestino(d) {
  if (!d) return '';
  const partes = [];
  if (d.ciudad) partes.push(d.ciudad);
  if (d.estado_provincia && d.estado_provincia !== d.ciudad) partes.push(d.estado_provincia);
  if (d.pais && d.pais !== d.ciudad && d.pais !== d.estado_provincia) partes.push(d.pais);
  return partes.join(', ');
}

function renderHotelesConDestinos(hoteles, maxMostrar) {
  if (maxMostrar === undefined) maxMostrar = 3;
  if (!hoteles || hoteles.length === 0) return '<div style="color:var(--text-muted);padding:2px 0;">—</div>';
  let html = '';
  const mostrar = hoteles.slice(0, maxMostrar);
  const extra = hoteles.length - maxMostrar;
  mostrar.forEach(function(h) {
    var nom = escapeHtml(h.nombre);
    var destStr = h.destino ? formatearDestino(h.destino) : '';
    html += '<div style="padding:2px 0;line-height:1.35;">• ' + nom;
    if (destStr) html += ' <span style="color:var(--text-muted);">— ' + escapeHtml(destStr) + '</span>';
    html += '</div>';
  });
  if (extra > 0) {
    html += '<div style="padding:2px 0;color:var(--text-muted);font-size:0.72rem;">+ ' + extra + ' m&aacute;s</div>';
  }
  return html;
}

function renderizarPaquetes(paquetes) {
  const grid = document.getElementById('paquetesGrid');
  const empty = document.getElementById('emptyPaquetes');
  const total = document.getElementById('totalPaquetes');

  total.innerHTML = '<strong>' + paquetes.length + '</strong> paquetes';

  if (paquetes.length === 0) {
    grid.innerHTML = '';
    empty.style.display = 'block';
    return;
  }

  empty.style.display = 'none';

  const BASE = API_BASE.replace('/api', '');

  grid.innerHTML = paquetes.map(p => {
    const imgHtml = p.imagen_principal
      ? '<img src="' + BASE + p.imagen_principal + '" alt="' + escapeHtml(p.nombre) + '" loading="lazy">'
      : '<div class="placeholder"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg><span>Sin imagen</span></div>';

    const fechas = p.fecha_partida + (p.fecha_vuelta ? ' → ' + p.fecha_vuelta : '');
    const precio = parseFloat(p.precio).toFixed(2);
    const disponible = p.disponible;
    const descCorta = p.descripcion ? escapeHtml(p.descripcion.substring(0, 120)) : '';

    return `
      <div class="pkg-card">
        <div class="pkg-card__img">${imgHtml}</div>
        <div class="pkg-card__body">
          <div class="pkg-card__name">${escapeHtml(p.nombre)}</div>
          ${descCorta ? '<div class="pkg-card__desc">' + descCorta + (p.descripcion.length > 120 ? '...' : '') + '</div>' : ''}
          <div class="pkg-card__meta">
            <span style="flex-basis:100%;margin-bottom:2px;"><strong>Hoteles:</strong></span>
            ${renderHotelesConDestinos(p.hoteles)}
            <span>Fechas: ${escapeHtml(fechas)}</span>
          </div>
          <div class="pkg-card__footer">
            <span class="pkg-card__price">$${precio}</span>
            <span class="badge-available ${disponible ? 'si' : 'no'}">${disponible ? 'Disponible' : 'No disponible'}</span>
            <div class="pkg-card__actions">
              <button class="btn-icon" type="button" onclick="abrirModalEditar(${p.id})" title="Editar paquete">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

// ----- Filters -----

function aplicarFiltros() {
  cargarPaquetes();
}

function limpiarFiltros() {
  document.getElementById('filtroNombre').value = '';
  document.getElementById('filtroMesPartida').value = '';
  document.getElementById('filtroDestino').value = '';
  document.getElementById('filtroOrdenPrecio').value = '';
  cargarPaquetes();
}

// ----- Modal -----

function abrirModalCrear() {
  editandoId = null;
  document.getElementById('modalPaqueteTitulo').textContent = 'Agregar Paquete';
  document.getElementById('paqueteId').value = '';
  document.getElementById('paqueteNombre').value = '';
  document.getElementById('paqueteDescripcion').value = '';
  document.getElementById('paqueteFechaPartida').value = '';
  document.getElementById('paqueteFechaVuelta').value = '';
  document.getElementById('paquetePrecio').value = '';
  document.getElementById('paqueteDisponible').checked = true;
  const preview = document.getElementById('previewImagen');
  preview.innerHTML = '<span>+</span>';
  preview.className = 'preview sin-imagen';
  document.getElementById('imagenInput').value = '';
  document.getElementById('btnQuitarImagen').style.display = 'none';
  const previewSec = document.getElementById('previewImagenSecundaria');
  previewSec.innerHTML = '<span>+</span>';
  previewSec.className = 'preview sin-imagen';
  document.getElementById('imagenSecundariaInput').value = '';
  document.getElementById('btnQuitarImagenSecundaria').style.display = 'none';
  document.getElementById('errorValidacion').style.display = 'none';
  document.getElementById('btnGuardarPaquete').textContent = 'Guardar';
  document.getElementById('buscarHoteles').value = '';
  document.getElementById('btnEliminarPaquete').style.display = 'none';
  cargarHoteles();
  document.getElementById('modalPaquete').classList.add('open');
}

function abrirModalEditar(id) {
  const p = paquetesData.find(pkg => pkg.id === id);
  if (!p) return;

  editandoId = id;
  document.getElementById('modalPaqueteTitulo').textContent = 'Modificar Paquete';
  document.getElementById('paqueteId').value = id;
  document.getElementById('paqueteNombre').value = p.nombre;
  document.getElementById('paqueteDescripcion').value = p.descripcion || '';
  document.getElementById('paqueteFechaPartida').value = p.fecha_partida;
  document.getElementById('paqueteFechaVuelta').value = p.fecha_vuelta || '';
  document.getElementById('paquetePrecio').value = p.precio;
  document.getElementById('paqueteDisponible').checked = p.disponible;
  document.getElementById('errorValidacion').style.display = 'none';
  document.getElementById('btnGuardarPaquete').textContent = 'Actualizar';
  document.getElementById('btnEliminarPaquete').style.display = 'inline-flex';
  document.getElementById('buscarHoteles').value = '';

  const BASE = API_BASE.replace('/api', '');
  const preview = document.getElementById('previewImagen');
  if (p.imagen_principal) {
    preview.innerHTML = '<img src="' + BASE + p.imagen_principal + '" alt="Imagen actual">';
    preview.className = 'preview';
  } else {
    preview.innerHTML = '<span>+</span>';
    preview.className = 'preview sin-imagen';
  }
  document.getElementById('imagenInput').value = '';
  document.getElementById('btnQuitarImagen').style.display = 'none';

  const previewSec = document.getElementById('previewImagenSecundaria');
  if (p.imagen_secundaria) {
    previewSec.innerHTML = '<img src="' + BASE + p.imagen_secundaria + '" alt="Imagen secundaria actual">';
    previewSec.className = 'preview';
  } else {
    previewSec.innerHTML = '<span>+</span>';
    previewSec.className = 'preview sin-imagen';
  }
  document.getElementById('imagenSecundariaInput').value = '';
  document.getElementById('btnQuitarImagenSecundaria').style.display = 'none';

  cargarHoteles().then(() => {
    const select = document.getElementById('paqueteHoteles');
    const paqHotelIds = p.hoteles.map(h => h.id);
    Array.from(select.options).forEach(opt => {
      if (opt.value && paqHotelIds.includes(parseInt(opt.value, 10))) {
        opt.selected = true;
      }
    });
  });

  document.getElementById('modalPaquete').classList.add('open');
}

function cerrarModal() {
  document.getElementById('modalPaquete').classList.remove('open');
}

function previewImagen(input) {
  const preview = document.getElementById('previewImagen');
  const btnQuitar = document.getElementById('btnQuitarImagen');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
      preview.className = 'preview';
      btnQuitar.style.display = 'inline-flex';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function quitarImagen() {
  document.getElementById('imagenInput').value = '';
  const p = editandoId ? paquetesData.find(pkg => pkg.id === editandoId) : null;
  const preview = document.getElementById('previewImagen');
  if (p && p.imagen_principal) {
    const BASE = API_BASE.replace('/api', '');
    preview.innerHTML = '<img src="' + BASE + p.imagen_principal + '" alt="Imagen actual">';
    preview.className = 'preview';
  } else {
    preview.innerHTML = '<span>+</span>';
    preview.className = 'preview sin-imagen';
  }
  document.getElementById('btnQuitarImagen').style.display = 'none';
}

function previewImagenSecundaria(input) {
  const preview = document.getElementById('previewImagenSecundaria');
  const btnQuitar = document.getElementById('btnQuitarImagenSecundaria');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview secundaria">';
      preview.className = 'preview';
      btnQuitar.style.display = 'inline-flex';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function quitarImagenSecundaria() {
  document.getElementById('imagenSecundariaInput').value = '';
  const p = editandoId ? paquetesData.find(pkg => pkg.id === editandoId) : null;
  const preview = document.getElementById('previewImagenSecundaria');
  if (p && p.imagen_secundaria) {
    const BASE = API_BASE.replace('/api', '');
    preview.innerHTML = '<img src="' + BASE + p.imagen_secundaria + '" alt="Imagen secundaria actual">';
    preview.className = 'preview';
  } else {
    preview.innerHTML = '<span>+</span>';
    preview.className = 'preview sin-imagen';
  }
  document.getElementById('btnQuitarImagenSecundaria').style.display = 'none';
}

// ----- Guardar -----

async function guardarPaquete() {
  const token = getToken();
  if (!token) {
    mostrarToast('Sesión expirada. Volvé a iniciar sesión.', 'error');
    setTimeout(logout, 2000);
    return;
  }

  const nombre = document.getElementById('paqueteNombre').value.trim();
  const descripcion = document.getElementById('paqueteDescripcion').value.trim();
  const fechaPartida = document.getElementById('paqueteFechaPartida').value;
  const fechaVuelta = document.getElementById('paqueteFechaVuelta').value;
  const precio = document.getElementById('paquetePrecio').value.trim();
  const disponible = document.getElementById('paqueteDisponible').checked;
  const selectHoteles = document.getElementById('paqueteHoteles');
  const hotelesIds = Array.from(selectHoteles.selectedOptions).map(o => parseInt(o.value, 10)).filter(id => !isNaN(id));
  const imagenInput = document.getElementById('imagenInput');
  const errorEl = document.getElementById('errorValidacion');

  const errores = [];
  if (!nombre) errores.push('El nombre es obligatorio.');
  if (!fechaPartida) errores.push('La fecha de partida es obligatoria.');
  if (!precio) errores.push('El precio es obligatorio.');
  else if (isNaN(parseFloat(precio)) || parseFloat(precio) < 0) errores.push('El precio debe ser un número válido mayor o igual a 0.');
  if (hotelesIds.length === 0) errores.push('Debe seleccionar al menos un hotel.');

  if (errores.length > 0) {
    errorEl.textContent = errores.join(' ');
    errorEl.style.display = 'block';
    return;
  }
  errorEl.style.display = 'none';

  const formData = new FormData();
  formData.append('nombre', nombre);
  formData.append('descripcion', descripcion);
  formData.append('fecha_partida', fechaPartida);
  if (fechaVuelta) formData.append('fecha_vuelta', fechaVuelta);
  formData.append('precio', precio);
  formData.append('disponible', disponible ? '1' : '0');
  formData.append('hoteles_ids', JSON.stringify(hotelesIds));
  if (imagenInput.files && imagenInput.files[0]) {
    formData.append('imagen_principal', imagenInput.files[0]);
  }
  const imagenSecundariaInput = document.getElementById('imagenSecundariaInput');
  if (imagenSecundariaInput.files && imagenSecundariaInput.files[0]) {
    formData.append('imagen_secundaria', imagenSecundariaInput.files[0]);
  }

  const btn = document.getElementById('btnGuardarPaquete');
  btn.disabled = true;
  btn.textContent = 'Guardando...';

  try {
    const url = editandoId ? '/paquetes/' + editandoId : '/paquetes';
    await enviarFormData('POST', url, formData);
    mostrarToast(editandoId ? 'Paquete actualizado correctamente.' : 'Paquete creado correctamente.', 'success');
    cerrarModal();
    await cargarPaquetes();
  } catch (e) {
    mostrarToast(e.message, 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = editandoId ? 'Actualizar' : 'Guardar';
  }
}

// ----- Hotel filter -----

function filtrarHoteles(valor) {
  const term = valor.toLowerCase().trim();
  const select = document.getElementById('paqueteHoteles');
  Array.from(select.options).forEach(opt => {
    if (!opt.value) return;
    opt.style.display = '';
  });
  if (!term) return;
  const selectedValues = Array.from(select.selectedOptions).map(o => o.value);
  Array.from(select.options).forEach(opt => {
    if (!opt.value) return;
    const texto = opt.textContent.toLowerCase();
    if (!texto.includes(term)) {
      opt.style.display = 'none';
    }
  });
  selectedValues.forEach(v => {
    const opt = Array.from(select.options).find(o => o.value === v);
    if (opt) opt.selected = true;
  });
}

// ----- Delete -----

async function eliminarPaquete() {
  if (!editandoId) return;
  if (!confirm('¿Seguro que querés eliminar este paquete?')) return;

  const token = getToken();
  if (!token) {
    mostrarToast('Sesión expirada. Volvé a iniciar sesión.', 'error');
    setTimeout(logout, 2000);
    return;
  }

  const btn = document.getElementById('btnEliminarPaquete');
  btn.disabled = true;
  btn.textContent = 'Eliminando...';

  try {
    const r = await fetch(API_BASE + '/paquetes/' + editandoId, {
      method: 'DELETE',
      headers: { 'Authorization': 'Bearer ' + token },
    });
    const d = await r.json();
    if (!r.ok) {
      throw new Error(d.error || 'Error al eliminar');
    }
    mostrarToast('Paquete eliminado correctamente.', 'success');
    cerrarModal();
    await cargarPaquetes();
  } catch (e) {
    mostrarToast(e.message, 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Eliminar';
  }
}

// ----- Helpers -----

function escapeHtml(text) {
  if (text === null || text === undefined) return '';
  const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
  return String(text).replace(/[&<>"']/g, c => map[c]);
}

function mostrarToast(mensaje, tipo) {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const toast = document.createElement('div');
  toast.className = 'toast toast-' + tipo;
  toast.textContent = mensaje;
  container.appendChild(toast);
  setTimeout(() => { toast.remove(); }, 3500);
}
</script>

<?php require_once __DIR__ . '/components/layout-end.php'; ?>
