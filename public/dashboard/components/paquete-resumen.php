<?php

$paqueteResumenPrefix = $paqueteResumenPrefix ?? 'paqueteResumen';
$paqueteResumenToggleAction = $paqueteResumenToggleAction ?? '';
?>
<div class="paquete-resumen" id="<?= htmlspecialchars($paqueteResumenPrefix, ENT_QUOTES) ?>Panel" style="display:none;">
  <div class="paquete-resumen__header">
    <div>
      <div class="paquete-resumen__eyebrow">Paquete seleccionado</div>
      <h4 class="paquete-resumen__title" id="<?= htmlspecialchars($paqueteResumenPrefix, ENT_QUOTES) ?>Titulo"></h4>
      <div class="paquete-resumen__place" id="<?= htmlspecialchars($paqueteResumenPrefix, ENT_QUOTES) ?>Lugar"></div>
    </div>
    <span class="paquete-resumen__id" id="<?= htmlspecialchars($paqueteResumenPrefix, ENT_QUOTES) ?>Id"></span>
  </div>

  <div class="paquete-resumen__meta">
    <div class="paquete-resumen__meta-item">
      <span>Fechas</span>
      <strong id="<?= htmlspecialchars($paqueteResumenPrefix, ENT_QUOTES) ?>Fechas"></strong>
    </div>
    <div class="paquete-resumen__meta-item">
      <span>Precio</span>
      <strong id="<?= htmlspecialchars($paqueteResumenPrefix, ENT_QUOTES) ?>Precio"></strong>
    </div>
    <div class="paquete-resumen__meta-item">
      <span>Disponibilidad</span>
      <strong id="<?= htmlspecialchars($paqueteResumenPrefix, ENT_QUOTES) ?>Disponibilidad"></strong>
    </div>
  </div>

  <button class="paquete-resumen__toggle" type="button" id="<?= htmlspecialchars($paqueteResumenPrefix, ENT_QUOTES) ?>Toggle" <?= $paqueteResumenToggleAction ? 'onclick="' . htmlspecialchars($paqueteResumenToggleAction, ENT_QUOTES) . '"' : '' ?>>Ver más detalles</button>

  <div class="paquete-resumen__details" id="<?= htmlspecialchars($paqueteResumenPrefix, ENT_QUOTES) ?>Detalles" style="display:none;">
    <div class="paquete-resumen__detail-item">
      <span>Pileta</span>
      <strong id="<?= htmlspecialchars($paqueteResumenPrefix, ENT_QUOTES) ?>Pileta"></strong>
    </div>
    <div class="paquete-resumen__detail-item">
      <span>All inclusive</span>
      <strong id="<?= htmlspecialchars($paqueteResumenPrefix, ENT_QUOTES) ?>AllInclusive"></strong>
    </div>
    <div class="paquete-resumen__detail-item paquete-resumen__detail-item--full">
      <span>Hoteles</span>
      <div class="paquete-resumen__hoteles" id="<?= htmlspecialchars($paqueteResumenPrefix, ENT_QUOTES) ?>Hoteles"></div>
    </div>
  </div>
</div>
