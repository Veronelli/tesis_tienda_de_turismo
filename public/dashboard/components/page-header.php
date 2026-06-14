<?php
/**
 * Page header component – Material Design 3
 *
 * Usage: require __DIR__ . '/page-header.php';
 *
 * @param string $title Title of the page
 * @param bool   $showFab Whether to show the "Agregar" FAB button
 * @param string $fabAction JavaScript action for the FAB button
 */
$title = $title ?? '';
$showFab = $showFab ?? false;
$fabAction = $fabAction ?? '';
?>
<style>
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
  gap: 12px;
}

.page-header__title {
  font-size: 1rem;
  font-weight: 500;
  color: var(--text);
  margin: 0;
}

.md3-fab {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  height: 40px;
  padding: 0 16px;
  border: none;
  border-radius: 20px;
  background: var(--accent);
  color: #ffffff;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(255, 124, 0, 0.3);
  transition: background 0.2s, box-shadow 0.2s;
}

.md3-fab:hover {
  background: var(--accent-hover);
  box-shadow: 0 4px 12px rgba(255, 124, 0, 0.4);
}

.md3-fab:active {
  transform: scale(0.98);
}

.md3-fab:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.md3-fab__icon {
  width: 18px;
  height: 18px;
}

@media (max-width: 480px) {
  .page-header { flex-wrap: wrap; }
}
</style>

<div class="page-header">
  <h2 class="page-header__title"><?= htmlspecialchars($title, ENT_QUOTES) ?></h2>
  <?php if ($showFab): ?>
  <button class="md3-fab" id="btn-agregar" type="button" <?= $fabAction ? "onclick=\"$fabAction\"" : '' ?>>
    <svg class="md3-fab__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Agregar
  </button>
  <?php endif; ?>
</div>