<?php

$title = 'Consultas';
$currentPage = 'consultas';

require_once __DIR__ . '/components/layout-start.php';
require_once __DIR__ . '/components/page-header.php';
?>

<div class="card">
  <div class="card-header">
    <h3>Listado de Consultas</h3>
    <span class="total-badge" id="totalConsultas"><strong>0</strong> consultas</span>
  </div>
  <div class="card-body">
    <div class="consultas-filters">
      <div class="field">
        <label>Cliente</label>
        <input type="text" id="filtroCliente" class="form-control" placeholder="Buscar por nombre, apellido, email o DNI...">
      </div>
      <div class="field">
        <label>Paquete</label>
        <input type="text" id="filtroPaquete" class="form-control" placeholder="Nombre del paquete...">
      </div>
      <div class="field">
        <label>Estado</label>
        <select id="filtroEstado" class="form-control">
          <option value="">Todos</option>
          <option value="pendiente">Pendiente</option>
          <option value="procesando">Procesando</option>
          <option value="cancelada">Cancelada</option>
          <option value="completada">Completada</option>
        </select>
      </div>
      <div class="field">
        <label>Calificación</label>
        <select id="filtroCalificacion" class="form-control">
          <option value="">Todas</option>
          <option value="frio">Frio</option>
          <option value="tibio">Tibio</option>
          <option value="caliente">Caliente</option>
        </select>
      </div>
      <div class="field">
        <label>Fecha desde</label>
        <input type="date" id="filtroFechaDesde" class="form-control">
      </div>
      <div class="field">
        <label>Fecha hasta</label>
        <input type="date" id="filtroFechaHasta" class="form-control">
      </div>
      <div class="field field-actions">
        <button class="btn btn-primary btn-sm" type="button" onclick="buscarConsultas()">Buscar</button>
        <button class="btn btn-secondary btn-sm" type="button" onclick="limpiarFiltros()">Limpiar</button>
      </div>
    </div>
    <div class="loading" id="loadingConsultas">
      <div class="spinner"></div>
      Cargando consultas...
    </div>
    <div id="tablaConsultas" style="display:none;">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Paquete</th>
            <th>Mensaje</th>
            <th>Calificación</th>
            <th>Estado</th>
            <th>Fecha</th>
            <th class="text-right">Acciones</th>
          </tr>
        </thead>
        <tbody id="consultasTbody"></tbody>
      </table>
      <div class="empty-state" id="emptyConsultas" style="display:none;">No hay consultas registradas.</div>
    </div>
  </div>
</div>

<!-- Modal ver/editar consulta -->
<div class="modal-overlay" id="modalConsulta">
  <div class="modal">
    <div class="modal-header">
      <h3 id="modalConsultaTitulo">Ver Consulta</h3>
      <button class="modal-close" type="button" onclick="cerrarModal()" aria-label="Cerrar">&times;</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="consultaId">
      <div class="form-row">
        <div class="form-group">
          <label>Cliente</label>
          <p class="form-value" id="consultaCliente"></p>
        </div>
        <div class="form-group">
          <label>DNI</label>
          <p class="form-value" id="consultaDni"></p>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Email</label>
          <p class="form-value" id="consultaEmail"></p>
        </div>
        <div class="form-group">
          <label>Teléfono</label>
          <p class="form-value" id="consultaTelefono"></p>
        </div>
      </div>
      <div class="form-group">
        <label>Paquete consultado</label>
        <p class="form-value" id="consultaPaquete"></p>
      </div>
      <div class="form-group">
        <label>Mensaje</label>
        <textarea id="consultaMensaje" class="form-control" rows="4"></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Calificación</label>
          <p class="form-value" id="consultaCalificacion"></p>
        </div>
        <div class="form-group">
          <label>Estado</label>
          <select id="consultaEstado" class="form-control">
            <option value="pendiente">Pendiente</option>
            <option value="procesando">Procesando</option>
            <option value="cancelada">Cancelada</option>
            <option value="completada">Completada</option>
          </select>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" type="button" onclick="cerrarModal()">Cancelar</button>
      <button class="btn btn-primary" type="button" id="btnGuardarConsulta" onclick="guardarConsulta()">Guardar cambios</button>
    </div>
  </div>
</div>

<style>
.consultas-filters {
  display: flex;
  gap: 12px;
  align-items: end;
  flex-wrap: wrap;
  margin-bottom: 14px;
}
.consultas-filters .field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.consultas-filters .field label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.consultas-filters .field input,
.consultas-filters .field select {
  min-width: 150px;
  height: 36px;
  font-size: 0.82rem;
}
.consultas-filters .field-actions {
  flex-direction: row;
  align-items: end;
  gap: 6px;
}
.consultas-filters .field-actions .btn {
  height: 36px;
  font-size: 0.82rem;
  padding: 0 14px;
  white-space: nowrap;
}
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
.data-table .btn-icon-danger:hover {
  color: var(--status-cancelada);
  border-color: var(--status-cancelada);
}
.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: var(--text-muted);
  font-size: 0.88rem;
}
.badge-estado {
  display: inline-block;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 100px;
}
.badge-estado.pendiente {
  background: rgba(245,158,11,0.12);
  color: var(--status-proceso);
}
.badge-estado.procesando {
  background: rgba(34,197,94,0.12);
  color: var(--status-concretada);
}
.badge-estado.cancelada {
  background: rgba(239,68,68,0.12);
  color: var(--status-cancelada);
}
.badge-estado.completada {
  background: rgba(34,197,94,0.12);
  color: var(--status-concretada);
}
.badge-calificacion {
  display: inline-block;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 100px;
}
.badge-calificacion.frio {
  background: rgba(59,130,246,0.12);
  color: #2563eb;
}
.badge-calificacion.tibio {
  background: rgba(245,158,11,0.12);
  color: #d97706;
}
.badge-calificacion.caliente {
  background: rgba(249,115,22,0.12);
  color: #ea580c;
}
.badge-calificacion.sin-calificar {
  background: rgba(120,113,108,0.12);
  color: var(--text-muted);
}
.form-value {
  padding: 9px 12px;
  background: var(--bg);
  border: 1px solid var(--border-light);
  border-radius: 6px;
  font-size: 0.88rem;
  color: var(--text);
  min-height: 38px;
  display: flex;
  align-items: center;
}
.form-value-multiline {
  align-items: flex-start;
  white-space: pre-wrap;
}
.mensaje-truncate {
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.actions-group {
  display: inline-flex;
  gap: 4px;
}

@media (max-width: 768px) {
  .consultas-filters { flex-direction: column; align-items: stretch; }
  .consultas-filters .field input,
  .consultas-filters .field select { min-width: 0; }
  .data-table { font-size: 0.78rem; }
  .data-table tbody td { padding: 8px 6px; }
  .mensaje-truncate { max-width: 80px; }
}
</style>

<script src="js/auth.js"></script>
<script>
let consultasData = [];

document.addEventListener('DOMContentLoaded', () => {
  cargarConsultas();
});

function getFiltros() {
  const cliente = document.getElementById('filtroCliente').value.trim();
  const paquete = document.getElementById('filtroPaquete').value.trim().toLowerCase();
  const estado = document.getElementById('filtroEstado').value;
  const calificacion = document.getElementById('filtroCalificacion').value;
  const fechaDesde = document.getElementById('filtroFechaDesde').value;
  const fechaHasta = document.getElementById('filtroFechaHasta').value;
  return { cliente, paquete, estado, calificacion, fechaDesde, fechaHasta };
}

async function cargarConsultas() {
  const loading = document.getElementById('loadingConsultas');
  const tabla = document.getElementById('tablaConsultas');
  loading.style.display = 'flex';
  tabla.style.display = 'none';
  tabla.style.overflowX = 'auto';

  try {
    const filtros = getFiltros();
    const params = new URLSearchParams();

    if (filtros.cliente) params.set('cliente', filtros.cliente);
    if (filtros.estado) params.set('estado', filtros.estado);
    if (filtros.calificacion) params.set('calificacion', filtros.calificacion);
    if (filtros.fechaDesde) params.set('fecha_desde', filtros.fechaDesde);
    if (filtros.fechaHasta) params.set('fecha_hasta', filtros.fechaHasta);

    const qs = params.toString();
    const path = '/consultas' + (qs ? '?' + qs : '');
    consultasData = await api('GET', path);

    let data = consultasData;
    if (filtros.paquete) {
      data = data.filter(c =>
        c.paquete && c.paquete.nombre && c.paquete.nombre.toLowerCase().includes(filtros.paquete)
      );
    }

    renderizarTabla(data);
    loading.style.display = 'none';
    tabla.style.display = 'block';
  } catch (e) {
    loading.innerHTML = '<p class="error-message">Error al cargar consultas.</p>';
    mostrarToast(e.message, 'error');
  }
}

function buscarConsultas() {
  cargarConsultas();
}

function limpiarFiltros() {
  document.getElementById('filtroCliente').value = '';
  document.getElementById('filtroPaquete').value = '';
  document.getElementById('filtroEstado').value = '';
  document.getElementById('filtroCalificacion').value = '';
  document.getElementById('filtroFechaDesde').value = '';
  document.getElementById('filtroFechaHasta').value = '';
  cargarConsultas();
}

function renderizarTabla(consultas) {
  const tbody = document.getElementById('consultasTbody');
  const empty = document.getElementById('emptyConsultas');
  const total = document.getElementById('totalConsultas');

  total.innerHTML = '<strong>' + consultas.length + '</strong> consultas';

  if (consultas.length === 0) {
    tbody.innerHTML = '';
    empty.style.display = 'block';
    return;
  }

  empty.style.display = 'none';
  tbody.innerHTML = consultas.map(c => {
    const clienteNombre = c.cliente ? escapeHtml(c.cliente.nombre + ' ' + c.cliente.apellido) : '—';
    const clienteEmail = c.cliente ? escapeHtml(c.cliente.email) : '—';
    const paqueteNombre = c.paquete ? escapeHtml(c.paquete.nombre) : '—';
    const mensaje = escapeHtml(c.mensaje || '');
    const calificacion = normalizarCalificacion(c.calificacion);
    const calificacionLabel = formatearCalificacion(c.calificacion);
    const estado = String(c.estado || 'pendiente').trim().toLowerCase();
    const estadoLabel = formatearEstado(estado);
    const fecha = c.fecha_creacion ? c.fecha_creacion.split(' ')[0] : '—';

    return `
    <tr>
      <td>${c.id}</td>
      <td>
        <strong>${clienteNombre}</strong>
        <div style="font-size:0.75rem;color:var(--text-muted)">${clienteEmail}</div>
        <div style="font-size:0.75rem;color:var(--text-muted)">DNI: ${escapeHtml(c.cliente ? c.cliente.dni || '—' : '—')}</div>
      </td>
      <td>${paqueteNombre}</td>
      <td><span class="mensaje-truncate" title="${escapeHtml(mensaje)}">${mensaje}</span></td>
      <td><span class="badge-calificacion ${calificacion || 'sin-calificar'}">${escapeHtml(calificacionLabel)}</span></td>
      <td><span class="badge-estado ${estado}">${estadoLabel}</span></td>
      <td>${fecha}</td>
      <td class="text-right">
        <div class="actions-group">
          <button class="btn-icon" type="button" onclick="abrirModalConsulta(${c.id})" title="Ver detalle">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </td>
    </tr>
  `}).join('');
}

function abrirModalConsulta(id) {
  const consulta = consultasData.find(c => c.id === id);
  if (!consulta) return;

  document.getElementById('modalConsultaTitulo').textContent = 'Editar Consulta #' + id;
  document.getElementById('consultaId').value = id;

  const cliente = consulta.cliente || {};
  document.getElementById('consultaCliente').textContent = (cliente.nombre || '') + ' ' + (cliente.apellido || '');
  document.getElementById('consultaDni').textContent = cliente.dni || '—';
  document.getElementById('consultaEmail').textContent = cliente.email || '—';
  document.getElementById('consultaTelefono').textContent = cliente.telefono || '—';
  const paquete = consulta.paquete || {};
  document.getElementById('consultaPaquete').textContent = paquete.nombre || '—';
  document.getElementById('consultaMensaje').value = consulta.mensaje || '';
  document.getElementById('consultaCalificacion').textContent = formatearCalificacion(consulta.calificacion);
  document.getElementById('consultaEstado').value = String(consulta.estado || 'pendiente').trim().toLowerCase();
  document.getElementById('modalConsulta').classList.add('open');
}

function cerrarModal() {
  document.getElementById('modalConsulta').classList.remove('open');
}

async function guardarConsulta() {
  const id = document.getElementById('consultaId').value;
  const mensaje = document.getElementById('consultaMensaje').value.trim();
  const estado = document.getElementById('consultaEstado').value;

  if (!mensaje) {
    mostrarToast('El mensaje es obligatorio.', 'error');
    return;
  }

  if (!estado) {
    mostrarToast('El estado es obligatorio.', 'error');
    return;
  }

  const estadosValidos = ['pendiente', 'procesando', 'cancelada', 'completada'];
  if (!estadosValidos.includes(estado)) {
    mostrarToast('Estado inválido.', 'error');
    return;
  }

  const body = { mensaje, estado };
  const btn = document.getElementById('btnGuardarConsulta');
  btn.disabled = true;
  btn.textContent = 'Guardando...';

  try {
    await api('PUT', '/consultas/' + id, body);
    mostrarToast('Consulta actualizada correctamente.', 'success');
    cerrarModal();
    await cargarConsultas();
  } catch (e) {
    mostrarToast(e.message, 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Guardar cambios';
  }
}

function escapeHtml(text) {
  const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
  return String(text).replace(/[&<>"']/g, c => map[c]);
}

function normalizarCalificacion(calificacion) {
  if (!calificacion) return '';
  const valor = String(calificacion).trim().toLowerCase();
  return ['frio', 'tibio', 'caliente'].includes(valor) ? valor : '';
}

function formatearCalificacion(calificacion) {
  const valor = normalizarCalificacion(calificacion);
  if (!valor) return 'Sin calificar';
  return { frio: 'Frio', tibio: 'Tibio', caliente: 'Caliente' }[valor] || 'Sin calificar';
}

function formatearEstado(estado) {
  const valor = String(estado || '').trim().toLowerCase();
    return {
    pendiente: 'Pendiente',
    procesando: 'Procesando',
    cancelada: 'Cancelada',
    completada: 'Completada',
  }[valor] || valor;
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
