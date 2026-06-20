<?php

$title = 'Hoteles';
$currentPage = 'hoteles';
$showFab = true;
$fabAction = 'abrirModalCrear()';

require_once __DIR__ . '/components/layout-start.php';
require_once __DIR__ . '/components/page-header.php';
?>

<div class="card">
  <div class="card-header">
    <h3>Listado de Hoteles</h3>
    <span class="total-badge" id="totalHoteles"><strong>0</strong> hoteles</span>
  </div>
  <div class="card-body">
    <div class="loading" id="loadingHoteles">
      <div class="spinner"></div>
      Cargando hoteles...
    </div>
    <div id="tablaHoteles" style="display:none;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Ubicación</th>
            <th>Destino</th>
            <th class="text-right">Acciones</th>
          </tr>
        </thead>
        <tbody id="hotelesTbody"></tbody>
      </table>
      <div class="empty-state" id="emptyHoteles" style="display:none;">No hay hoteles registrados.</div>
    </div>
  </div>
</div>

<!-- Modal crear/editar hotel -->
<div class="modal-overlay" id="modalHotel">
  <div class="modal">
    <div class="modal-header">
      <h3 id="modalHotelTitulo">Agregar Hotel</h3>
      <button class="modal-close" type="button" onclick="cerrarModal()" aria-label="Cerrar">&times;</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="hotelId">
      <div class="form-group">
        <label for="hotelNombre">Nombre del hotel</label>
        <input type="text" id="hotelNombre" class="form-control" placeholder="Ej: Sheraton" required maxlength="200">
      </div>
      <div class="form-group">
        <label for="hotelUbicacion">Ubicación</label>
        <input type="text" id="hotelUbicacion" class="form-control" placeholder="Ej: Av. Corrientes 1234" required maxlength="255">
      </div>
      <div class="form-group">
        <label for="hotelDestino">Destino</label>
        <div class="input-with-button">
          <select id="hotelDestino" class="form-control" required>
            <option value="">Seleccionar destino...</option>
          </select>
          <button class="btn btn-secondary btn-sm" type="button" onclick="window.open('destinos.php', '_blank')" title="Agregar destino">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" type="button" onclick="cerrarModal()">Cancelar</button>
      <button class="btn btn-primary" type="button" id="btnGuardarHotel" onclick="guardarHotel()">Guardar</button>
    </div>
  </div>
</div>

<style>
.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}
.data-table thead th {
  text-align: left;
  padding: 10px 12px;
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--text-muted);
  border-bottom: 2px solid var(--border-light);
}
.data-table tbody td {
  padding: 12px;
  border-bottom: 1px solid var(--border-light);
  color: var(--text);
  vertical-align: middle;
}
.data-table tbody tr:hover {
  background: var(--accent-light);
}
.data-table .text-right {
  text-align: right;
}
.data-table .btn-icon {
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
.data-table .btn-icon:hover {
  background: var(--bg);
  color: var(--accent);
  border-color: var(--accent);
}
.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: var(--text-muted);
  font-size: 0.88rem;
}
.input-with-button {
  display: flex;
  gap: 8px;
  align-items: center;
}
.input-with-button select {
  flex: 1;
}
.btn-sm {
  height: 38px;
  padding: 0 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
</style>

<script src="js/auth.js"></script>
<script>
let hotelesData = [];
let destinosData = [];
let editandoId = null;

document.addEventListener('DOMContentLoaded', () => {
  cargarHoteles();
});

async function cargarDestinos() {
  try {
    destinosData = await api('GET', '/destinos');
    const select = document.getElementById('hotelDestino');
    const currentValue = select.value;
    select.innerHTML = '<option value="">Seleccionar destino...</option>';
    destinosData.forEach(d => {
      const opt = document.createElement('option');
      opt.value = d.id;
      opt.textContent = d.ciudad + (d.estado_provincia ? ', ' + d.estado_provincia : '') + ' - ' + d.pais;
      select.appendChild(opt);
    });
    if (currentValue) select.value = currentValue;
  } catch (e) {
    mostrarToast('Error al cargar destinos: ' + e.message, 'error');
  }
}

async function cargarHoteles() {
  document.getElementById('loadingHoteles').style.display = 'flex';
  document.getElementById('tablaHoteles').style.display = 'none';
  try {
    hotelesData = await api('GET', '/hoteles');
    renderizarTabla(hotelesData);
    document.getElementById('loadingHoteles').style.display = 'none';
    document.getElementById('tablaHoteles').style.display = 'block';
  } catch (e) {
    document.getElementById('loadingHoteles').innerHTML = '<p class="error-message">Error al cargar hoteles.</p>';
    mostrarToast(e.message, 'error');
  }
}

function renderizarTabla(hoteles) {
  const tbody = document.getElementById('hotelesTbody');
  const empty = document.getElementById('emptyHoteles');
  const total = document.getElementById('totalHoteles');

  total.innerHTML = '<strong>' + hoteles.length + '</strong> hoteles';

  if (hoteles.length === 0) {
    tbody.innerHTML = '';
    empty.style.display = 'block';
    return;
  }

  empty.style.display = 'none';
  tbody.innerHTML = hoteles.map(h => {
    const destinoText = h.destino ? escapeHtml(h.destino.ciudad + ', ' + h.destino.pais) : '—';
    return `
    <tr>
      <td><strong>${escapeHtml(h.nombre)}</strong></td>
      <td>${escapeHtml(h.ubicacion)}</td>
      <td>${destinoText}</td>
      <td class="text-right">
        <button class="btn-icon" type="button" onclick="abrirModalEditar(${h.id})" title="Editar">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </button>
      </td>
    </tr>
  `}).join('');
}

function abrirModalCrear() {
  editandoId = null;
  document.getElementById('modalHotelTitulo').textContent = 'Agregar Hotel';
  document.getElementById('hotelId').value = '';
  document.getElementById('hotelNombre').value = '';
  document.getElementById('hotelUbicacion').value = '';
  document.getElementById('hotelDestino').value = '';
  document.getElementById('btnGuardarHotel').textContent = 'Guardar';
  cargarDestinos();
  document.getElementById('modalHotel').classList.add('open');
}

function abrirModalEditar(id) {
  const hotel = hotelesData.find(h => h.id === id);
  if (!hotel) return;

  editandoId = id;
  document.getElementById('modalHotelTitulo').textContent = 'Modificar Hotel';
  document.getElementById('hotelId').value = id;
  document.getElementById('hotelNombre').value = hotel.nombre;
  document.getElementById('hotelUbicacion').value = hotel.ubicacion;

  cargarDestinos().then(() => {
    document.getElementById('hotelDestino').value = hotel.destino_id;
  });

  document.getElementById('btnGuardarHotel').textContent = 'Actualizar';
  document.getElementById('modalHotel').classList.add('open');
}

function cerrarModal() {
  document.getElementById('modalHotel').classList.remove('open');
}

async function guardarHotel() {
  const nombre = document.getElementById('hotelNombre').value.trim();
  const ubicacion = document.getElementById('hotelUbicacion').value.trim();
  const destinoId = document.getElementById('hotelDestino').value;

  if (!nombre || !ubicacion || !destinoId) {
    mostrarToast('Todos los campos son obligatorios.', 'error');
    return;
  }

  const body = { nombre, ubicacion, destino_id: parseInt(destinoId, 10) };
  const btn = document.getElementById('btnGuardarHotel');
  btn.disabled = true;
  btn.textContent = 'Guardando...';

  try {
    if (editandoId) {
      await api('PUT', '/hoteles/' + editandoId, body);
      mostrarToast('Hotel actualizado correctamente.', 'success');
    } else {
      await api('POST', '/hoteles', body);
      mostrarToast('Hotel creado correctamente.', 'success');
    }
    cerrarModal();
    await cargarHoteles();
  } catch (e) {
    mostrarToast(e.message, 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = editandoId ? 'Actualizar' : 'Guardar';
  }
}

function escapeHtml(text) {
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
