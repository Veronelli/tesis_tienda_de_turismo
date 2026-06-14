<?php

$title = 'Consultas';
$currentPage = 'consultas';

require_once __DIR__ . '/components/layout-start.php';
require_once __DIR__ . '/components/page-header.php';
?>
<div class="card">
  <div class="card-header">
    <h3>Listado de Consultas</h3>
    <button class="btn btn-primary btn-sm">Nueva consulta</button>
  </div>
  <div class="card-body">
    <div class="search-bar">
      <div class="field">
        <label>Buscar</label>
        <input type="text" placeholder="Destino, hotel o título...">
      </div>
    </div>
    <div class="inquiry-grid" id="consultas-list">
      <div class="loading">
        <div class="spinner"></div>
        Cargando consultas...
      </div>
    </div>
  </div>
</div>
<script src="js/app.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  loadConsultas();
});

function loadConsultas() {
  const list = document.getElementById('consultas-list');
  list.innerHTML = '<div class="loading"><div class="spinner"></div> Cargando consultas...</div>';
}
</script>
<?php require_once __DIR__ . '/components/layout-end.php';