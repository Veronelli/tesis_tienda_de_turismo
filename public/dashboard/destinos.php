<?php

$title = 'Destinos';
$currentPage = 'destinos';
$showFab = true;
$fabAction = 'abrirModalCrear()';

require_once __DIR__ . '/components/layout-start.php';
require_once __DIR__ . '/components/page-header.php';
?>

<div class="card">
  <div class="card-header">
    <h3>Listado de Destinos</h3>
    <span class="total-badge" id="totalDestinos"><strong>0</strong> destinos</span>
  </div>
  <div class="card-body">
    <div class="loading" id="loadingDestinos">
      <div class="spinner"></div>
      Cargando destinos...
    </div>
    <div id="tablaDestinos" style="display:none;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Destino</th>
            <th>Provincia</th>
            <th>País</th>
            <th class="text-right">Acciones</th>
          </tr>
        </thead>
        <tbody id="destinosTbody"></tbody>
      </table>
      <div class="empty-state" id="emptyDestinos" style="display:none;">No hay destinos registrados.</div>
    </div>
  </div>
</div>

<!-- Modal crear/editar destino -->
<div class="modal-overlay" id="modalDestino">
  <div class="modal">
    <div class="modal-header">
      <h3 id="modalDestinoTitulo">Agregar Destino</h3>
      <button class="modal-close" type="button" onclick="cerrarModal()" aria-label="Cerrar">&times;</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="destinoId">
      <div class="form-group">
        <label for="destinoCiudad">Destino</label>
        <input type="text" id="destinoCiudad" class="form-control" placeholder="Ej: Buenos Aires" required maxlength="150">
      </div>
      <div class="form-group">
        <label for="destinoProvincia">Provincia</label>
        <input type="text" id="destinoProvincia" class="form-control" placeholder="Ej: Buenos Aires" required maxlength="150">
      </div>
      <div class="form-group">
        <label for="destinoPais">País</label>
        <input type="text" id="destinoPais" class="form-control" placeholder="Ej: Argentina" required maxlength="150">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" type="button" onclick="cerrarModal()">Cancelar</button>
      <button class="btn btn-primary" type="button" id="btnGuardarDestino" onclick="guardarDestino()">Guardar</button>
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
</style>

<script src="js/auth.js"></script>
<script>
let destinosData = [];
let editandoId = null;

document.addEventListener('DOMContentLoaded', () => {
  cargarDestinos();
});

async function cargarDestinos() {
  document.getElementById('loadingDestinos').style.display = 'flex';
  document.getElementById('tablaDestinos').style.display = 'none';
  try {
    destinosData = await api('GET', '/destinos');
    renderizarTabla(destinosData);
    document.getElementById('loadingDestinos').style.display = 'none';
    document.getElementById('tablaDestinos').style.display = 'block';
    document.getElementById('tablaDestinos').style.overflowX = 'auto';
  } catch (e) {
    document.getElementById('loadingDestinos').innerHTML = '<p class="error-message">Error al cargar destinos.</p>';
    mostrarToast(e.message, 'error');
  }
}

function renderizarTabla(destinos) {
  const tbody = document.getElementById('destinosTbody');
  const empty = document.getElementById('emptyDestinos');
  const total = document.getElementById('totalDestinos');

  total.innerHTML = '<strong>' + destinos.length + '</strong> destinos';

  if (destinos.length === 0) {
    tbody.innerHTML = '';
    empty.style.display = 'block';
    return;
  }

  empty.style.display = 'none';
  tbody.innerHTML = destinos.map(d => `
    <tr>
      <td>${escapeHtml(d.ciudad)}</td>
      <td>${escapeHtml(d.estado_provincia)}</td>
      <td>${escapeHtml(d.pais)}</td>
      <td class="text-right">
        <button class="btn-icon" type="button" onclick="abrirModalEditar(${d.id})" title="Editar">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </button>
      </td>
    </tr>
  `).join('');
}

function abrirModalCrear() {
  editandoId = null;
  document.getElementById('modalDestinoTitulo').textContent = 'Agregar Destino';
  document.getElementById('destinoId').value = '';
  document.getElementById('destinoCiudad').value = '';
  document.getElementById('destinoProvincia').value = '';
  document.getElementById('destinoPais').value = '';
  document.getElementById('btnGuardarDestino').textContent = 'Guardar';
  document.getElementById('modalDestino').classList.add('open');
}

function abrirModalEditar(id) {
  const destino = destinosData.find(d => d.id === id);
  if (!destino) return;

  editandoId = id;
  document.getElementById('modalDestinoTitulo').textContent = 'Modificar Destino';
  document.getElementById('destinoId').value = id;
  document.getElementById('destinoCiudad').value = destino.ciudad;
  document.getElementById('destinoProvincia').value = destino.estado_provincia;
  document.getElementById('destinoPais').value = destino.pais;
  document.getElementById('btnGuardarDestino').textContent = 'Actualizar';
  document.getElementById('modalDestino').classList.add('open');
}

function cerrarModal() {
  document.getElementById('modalDestino').classList.remove('open');
}

async function guardarDestino() {
  const ciudad = document.getElementById('destinoCiudad').value.trim();
  const estadoProvincia = document.getElementById('destinoProvincia').value.trim();
  const pais = document.getElementById('destinoPais').value.trim();

  if (!ciudad || !estadoProvincia || !pais) {
    mostrarToast('Todos los campos son obligatorios.', 'error');
    return;
  }

  const body = { ciudad, estado_provincia: estadoProvincia, pais };
  const btn = document.getElementById('btnGuardarDestino');
  btn.disabled = true;
  btn.textContent = 'Guardando...';

  try {
    if (editandoId) {
      await api('PUT', '/destinos/' + editandoId, body);
      mostrarToast('Destino actualizado correctamente.', 'success');
    } else {
      await api('POST', '/destinos', body);
      mostrarToast('Destino creado correctamente.', 'success');
    }
    cerrarModal();
    await cargarDestinos();
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

<?php require_once __DIR__ . '/components/layout-end.php';
