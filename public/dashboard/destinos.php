<?php

$title = 'Destinos';
$currentPage = 'destinos';

require_once __DIR__ . '/components/layout-start.php';
require_once __DIR__ . '/components/page-header.php';
?>
<div class="card">
  <div class="card-header">
    <h3>Listado de Destinos</h3>
  </div>
  <div class="card-body">
    <div class="loading">
      <div class="spinner"></div>
      Cargando destinos...
    </div>
  </div>
</div>
<script src="js/app.js"></script>
<?php require_once __DIR__ . '/components/layout-end.php';