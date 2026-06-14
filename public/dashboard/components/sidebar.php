<?php
/**
 * Sidebar component – Material Design 3 inspired
 *
 * Usage: require __DIR__ . '/components/sidebar.php';
 */
?>
<style>
[data-md3-component="sidebar"] {
  --sidebar-bg: #FF7D00;
  --sidebar-item-bg: #ff8a1f;
  --sidebar-item-hover: #ff9633;
  --sidebar-text: #ffffff;
  --sidebar-text-muted: rgba(255, 255, 255, 0.88);
  --sidebar-border: rgba(224, 99, 0, 0.35);
  --sidebar-width: 250px;
  --md3-shape-none: 0;
  --md3-motion: cubic-bezier(0.2, 0, 0, 1);
  --md3-font: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.md3-sidebar {
  width: var(--sidebar-width);
  height: calc(100vh - 52px);
  position: fixed;
  top: 52px;
  left: 0;
  z-index: 100;
  display: flex;
  flex-direction: column;
  background: var(--sidebar-bg);
  color: var(--sidebar-text);
  font-family: var(--md3-font);
  transition: transform 0.25s var(--md3-motion);
}

.md3-sidebar ~ .main {
  margin-left: var(--sidebar-width);
}

.md3-sidebar__nav {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 13px 7px;
}

.md3-sidebar__link {
  min-height: 42px;
  width: 100%;
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 0 14px;
  border: 0;
  border-radius: var(--md3-shape-none);
  background: var(--sidebar-item-bg);
  color: var(--sidebar-text);
  font-family: var(--md3-font);
  font-size: 0.75rem;
  font-weight: 600;
  text-align: left;
  cursor: pointer;
  transition: background 0.16s var(--md3-motion), transform 0.16s var(--md3-motion);
}

.md3-sidebar__link::after {
  content: '';
  width: 0;
  height: 0;
  margin-left: auto;
  border-top: 4px solid transparent;
  border-bottom: 4px solid transparent;
  border-left: 5px solid currentColor;
  opacity: 0.95;
}

.md3-sidebar__link:hover,
.md3-sidebar__link--active {
  background: var(--sidebar-item-hover);
}

.md3-sidebar__link:active {
  transform: scale(0.995);
}

.md3-sidebar__link:focus-visible {
  outline: 2px solid #ffffff;
  outline-offset: -3px;
}

.md3-sidebar__icon {
  width: 15px;
  height: 15px;
  flex-shrink: 0;
  color: var(--sidebar-text);
}

.md3-sidebar__overlay {
  display: none;
  position: fixed;
  inset: 52px 0 0;
  z-index: 90;
  background: rgba(0, 0, 0, 0.35);
}

@media (max-width: 768px) {
  .md3-sidebar {
    transform: translateX(0);
  }

  .app.sidebar-collapsed .md3-sidebar {
    transform: translateX(-100%);
  }

  .app.sidebar-collapsed .md3-sidebar ~ .md3-sidebar__overlay {
    display: none;
  }

  .app:not(.sidebar-collapsed) .md3-sidebar ~ .md3-sidebar__overlay {
    display: block;
  }

  .md3-sidebar ~ .main {
    margin-left: 0;
  }
}
</style>

<aside class="md3-sidebar" data-md3-component="sidebar" aria-label="Navegación del panel">
  <nav class="md3-sidebar__nav" aria-label="Secciones">
    <button class="md3-sidebar__link md3-sidebar__link--active" data-section="consultas" type="button">
      <svg class="md3-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m12 7 1.55 3.14 3.45.5-2.5 2.44.59 3.44L12 14.9l-3.09 1.62.59-3.44L7 10.64l3.45-.5L12 7z"/></svg>
      Consultas
    </button>
    <button class="md3-sidebar__link" data-section="paquetes" type="button">
      <svg class="md3-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m12 7 1.55 3.14 3.45.5-2.5 2.44.59 3.44L12 14.9l-3.09 1.62.59-3.44L7 10.64l3.45-.5L12 7z"/></svg>
      Paquetes
    </button>
    <button class="md3-sidebar__link" data-section="clientes" type="button">
      <svg class="md3-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m12 7 1.55 3.14 3.45.5-2.5 2.44.59 3.44L12 14.9l-3.09 1.62.59-3.44L7 10.64l3.45-.5L12 7z"/></svg>
      Clientes
    </button>
    <button class="md3-sidebar__link" data-section="hoteles" type="button">
      <svg class="md3-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m12 7 1.55 3.14 3.45.5-2.5 2.44.59 3.44L12 14.9l-3.09 1.62.59-3.44L7 10.64l3.45-.5L12 7z"/></svg>
      Hoteles
    </button>
    <button class="md3-sidebar__link" data-section="destinos" type="button">
      <svg class="md3-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m12 7 1.55 3.14 3.45.5-2.5 2.44.59 3.44L12 14.9l-3.09 1.62.59-3.44L7 10.64l3.45-.5L12 7z"/></svg>
      Destinos
    </button>
    <button class="md3-sidebar__link" onclick="logout()" type="button">
      <svg class="md3-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m12 7 1.55 3.14 3.45.5-2.5 2.44.59 3.44L12 14.9l-3.09 1.62.59-3.44L7 10.64l3.45-.5L12 7z"/></svg>
      Salir
    </button>
  </nav>
</aside>

<div class="md3-sidebar__overlay" id="sidebarOverlay"></div>
