<?php

$title = 'Clientes';
$currentPage = 'clientes';
$showFab = true;
$fabAction = 'abrirModalCrear()';

require_once __DIR__ . '/components/layout-start.php';
require_once __DIR__ . '/components/page-header.php';
?>

<div class="card">
  <div class="card-header">
    <h3>Listado de Clientes</h3>
  </div>
  <div class="card-body">
    <div class="search-row">
      <input type="text" id="buscarCliente" class="form-control search-input" placeholder="Buscar por nombre, apellido, email o DNI..." onkeydown="if(event.key==='Enter')buscarClientes()">
      <button class="btn btn-primary btn-search" type="button" onclick="buscarClientes()">Buscar</button>
      <button class="btn btn-secondary btn-search" type="button" id="btnLimpiarBusqueda" onclick="limpiarBusqueda()">Limpiar</button>
      <span class="search-count" id="totalClientes"><strong>0</strong> clientes</span>
    </div>
    <div class="loading" id="loadingClientes">
      <div class="spinner"></div>
      Cargando clientes...
    </div>
    <div id="tablaClientes" style="display:none;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>DNI</th>
            <th>Ubicación</th>
            <th class="text-right">Acciones</th>
          </tr>
        </thead>
        <tbody id="clientesTbody"></tbody>
      </table>
      <div class="empty-state" id="emptyClientes" style="display:none;">No hay clientes registrados.</div>
    </div>
  </div>
</div>

<!-- Modal crear/editar cliente -->
<div class="modal-overlay" id="modalCliente">
  <div class="modal">
    <div class="modal-header">
      <h3 id="modalClienteTitulo">Agregar Cliente</h3>
      <button class="modal-close" type="button" onclick="cerrarModal()" aria-label="Cerrar">&times;</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="clienteId">
      <div class="form-group">
        <label for="clienteNombre">Nombre</label>
        <input type="text" id="clienteNombre" class="form-control" placeholder="Ej: Juan" required maxlength="100">
      </div>
      <div class="form-group">
        <label for="clienteApellido">Apellido</label>
        <input type="text" id="clienteApellido" class="form-control" placeholder="Ej: Pérez" required maxlength="100">
      </div>
      <div class="form-group">
        <label for="clienteEmail">Email</label>
        <input type="email" id="clienteEmail" class="form-control" placeholder="Ej: juan@example.com" required maxlength="255">
      </div>
      <div class="form-group">
        <label for="clienteTelefono">Teléfono</label>
        <input type="text" id="clienteTelefono" class="form-control" placeholder="Ej: 123456789" required maxlength="20">
      </div>
      <div class="form-group">
        <label for="clienteDni">DNI</label>
        <input type="text" id="clienteDni" class="form-control" placeholder="Ej: 12345678" required maxlength="20">
      </div>
      <div class="form-group">
        <label for="clienteUbicacion">Ubicación</label>
        <input type="text" id="clienteUbicacion" class="form-control" placeholder="Ej: Buenos Aires" required maxlength="255">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" type="button" onclick="cerrarModal()">Cancelar</button>
      <button class="btn btn-primary" type="button" id="btnGuardarCliente" onclick="guardarCliente()">Guardar</button>
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
.search-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 14px;
}
.search-input {
  flex: 1;
  max-width: 340px;
  height: 36px;
  font-size: 0.82rem;
}
.btn-search {
  height: 36px;
  font-size: 0.82rem;
  padding: 0 14px;
  white-space: nowrap;
}
.search-count {
  margin-left: auto;
  font-size: 0.82rem;
  color: var(--text-muted);
  white-space: nowrap;
}
</style>

<script src="js/auth.js"></script>
<script>
let clientesData = [];
let editandoId = null;
let ultimaBusqueda = '';

document.addEventListener('DOMContentLoaded', () => {
  cargarClientes();
});

function getFilteredData() {
  const q = ultimaBusqueda.toLowerCase().trim();
  if (!q) return clientesData;
  return clientesData.filter(c =>
    (c.nombre && c.nombre.toLowerCase().includes(q)) ||
    (c.apellido && c.apellido.toLowerCase().includes(q)) ||
    (c.email && c.email.toLowerCase().includes(q)) ||
    (c.dni && c.dni.toLowerCase().includes(q))
  );
}

async function cargarClientes() {
  document.getElementById('loadingClientes').style.display = 'flex';
  document.getElementById('tablaClientes').style.display = 'none';
  try {
    clientesData = await api('GET', '/clientes');
    renderizarTabla(getFilteredData());
    document.getElementById('loadingClientes').style.display = 'none';
    document.getElementById('tablaClientes').style.display = 'block';
    document.getElementById('tablaClientes').style.overflowX = 'auto';
  } catch (e) {
    document.getElementById('loadingClientes').innerHTML = '<p class="error-message">Error al cargar clientes.</p>';
    mostrarToast(e.message, 'error');
  }
}

function buscarClientes() {
  ultimaBusqueda = document.getElementById('buscarCliente').value;
  renderizarTabla(getFilteredData());
}

function limpiarBusqueda() {
  document.getElementById('buscarCliente').value = '';
  ultimaBusqueda = '';
  renderizarTabla(clientesData);
}

function renderizarTabla(clientes) {
  const tbody = document.getElementById('clientesTbody');
  const empty = document.getElementById('emptyClientes');
  const total = document.getElementById('totalClientes');

  total.innerHTML = '<strong>' + clientes.length + '</strong> clientes';

  if (clientes.length === 0) {
    tbody.innerHTML = '';
    empty.style.display = 'block';
    return;
  }

  empty.style.display = 'none';
  tbody.innerHTML = clientes.map(c => `
    <tr>
      <td><strong>${escapeHtml(c.nombre)}</strong></td>
      <td>${escapeHtml(c.apellido)}</td>
      <td>${escapeHtml(c.email)}</td>
      <td>${escapeHtml(c.telefono)}</td>
      <td>${escapeHtml(c.dni)}</td>
      <td>${escapeHtml(c.ubicacion)}</td>
      <td class="text-right">
        <button class="btn-icon" type="button" onclick="abrirModalEditar(${c.id})" title="Editar">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </button>
      </td>
    </tr>
  `).join('');
}

function abrirModalCrear() {
  editandoId = null;
  document.getElementById('modalClienteTitulo').textContent = 'Agregar Cliente';
  document.getElementById('clienteId').value = '';
  document.getElementById('clienteNombre').value = '';
  document.getElementById('clienteApellido').value = '';
  document.getElementById('clienteEmail').value = '';
  document.getElementById('clienteTelefono').value = '';
  document.getElementById('clienteDni').value = '';
  document.getElementById('clienteUbicacion').value = '';
  document.getElementById('btnGuardarCliente').textContent = 'Guardar';
  document.getElementById('modalCliente').classList.add('open');
}

function abrirModalEditar(id) {
  const cliente = clientesData.find(c => c.id === id);
  if (!cliente) return;

  editandoId = id;
  document.getElementById('modalClienteTitulo').textContent = 'Modificar Cliente';
  document.getElementById('clienteId').value = id;
  document.getElementById('clienteNombre').value = cliente.nombre;
  document.getElementById('clienteApellido').value = cliente.apellido;
  document.getElementById('clienteEmail').value = cliente.email;
  document.getElementById('clienteTelefono').value = cliente.telefono;
  document.getElementById('clienteDni').value = cliente.dni;
  document.getElementById('clienteUbicacion').value = cliente.ubicacion;
  document.getElementById('btnGuardarCliente').textContent = 'Actualizar';
  document.getElementById('modalCliente').classList.add('open');
}

function cerrarModal() {
  document.getElementById('modalCliente').classList.remove('open');
}

async function guardarCliente() {
  const nombre = document.getElementById('clienteNombre').value.trim();
  const apellido = document.getElementById('clienteApellido').value.trim();
  const email = document.getElementById('clienteEmail').value.trim();
  const telefono = document.getElementById('clienteTelefono').value.trim();
  const dni = document.getElementById('clienteDni').value.trim();
  const ubicacion = document.getElementById('clienteUbicacion').value.trim();

  if (!nombre || !apellido || !email || !telefono || !dni || !ubicacion) {
    mostrarToast('Todos los campos son obligatorios.', 'error');
    return;
  }

  const body = { nombre, apellido, email, telefono, dni, ubicacion };
  const btn = document.getElementById('btnGuardarCliente');
  btn.disabled = true;
  btn.textContent = 'Guardando...';

  try {
    if (editandoId) {
      await api('PUT', '/clientes/' + editandoId, body);
      mostrarToast('Cliente actualizado correctamente.', 'success');
    } else {
      await api('POST', '/clientes', body);
      mostrarToast('Cliente creado correctamente.', 'success');
    }
    cerrarModal();
    await cargarClientes();
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
