<?php
/**
 * Sidebar component – Material Design 3 inspired
 *
 * Usage: require __DIR__ . '/components/sidebar.php';
 *
 * @param string $currentPage The current page name for active state (e.g. 'destinos', 'consultas')
 */

$currentPage = $currentPage ?? '';
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
  text-decoration: none;
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

.md3-sidebar__footer {
  margin-top: auto;
  padding: 13px 7px;
  border-top: 1px solid var(--sidebar-border);
}

.md3-sidebar__logout {
  min-height: 42px;
  width: 100%;
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 0 14px;
  border: 0;
  border-radius: var(--md3-shape-none);
  background: transparent;
  color: var(--sidebar-text-muted);
  font-family: var(--md3-font);
  font-size: 0.75rem;
  font-weight: 600;
  text-align: left;
  text-decoration: none;
  cursor: pointer;
  transition: background 0.16s var(--md3-motion);
}

.md3-sidebar__logout:hover {
  background: rgba(255, 255, 255, 0.1);
  color: var(--sidebar-text);
}

@media (max-width: 768px) {
  .md3-sidebar {
    transform: translateX(-100%);
  }

  .app.sidebar-open .md3-sidebar {
    transform: translateX(0);
  }

  .app.sidebar-open .md3-sidebar ~ .md3-sidebar__overlay {
    display: block;
  }

  .md3-sidebar ~ .main {
    margin-left: 0;
  }
}
</style>

<aside class="md3-sidebar" data-md3-component="sidebar" aria-label="Navegación del panel">
  <nav class="md3-sidebar__nav" aria-label="Secciones">
    <a class="md3-sidebar__link <?= $currentPage === 'consultas' ? 'md3-sidebar__link--active' : '' ?>" href="consultas.php">
      <svg class="md3-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m12 7 1.55 3.14 3.45.5-2.5 2.44.59 3.44L12 14.9l-3.09 1.62.59-3.44L7 10.64l3.45-.5L12 7z"/></svg>
      Consultas
    </a>
    <a class="md3-sidebar__link <?= $currentPage === 'paquetes' ? 'md3-sidebar__link--active' : '' ?>" href="paquetes.php">
      <svg class="md3-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      Paquetes
    </a>
    <a class="md3-sidebar__link <?= $currentPage === 'clientes' ? 'md3-sidebar__link--active' : '' ?>" href="clientes.php">
      <svg class="md3-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      Clientes
    </a>
    <a class="md3-sidebar__link <?= $currentPage === 'hoteles' ? 'md3-sidebar__link--active' : '' ?>" href="hoteles.php">
      <svg class="md3-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/></svg>
      Hoteles
    </a>
    <a class="md3-sidebar__link <?= $currentPage === 'destinos' ? 'md3-sidebar__link--active' : '' ?>" href="destinos.php">
      <svg class="md3-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      Destinos
    </a>
  </nav>
  <div class="md3-sidebar__footer">
    <a class="md3-sidebar__logout" href="#" onclick="logout(); return false;">
      <svg class="md3-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Salir
    </a>
  </div>
</aside>

<div class="md3-sidebar__overlay" id="sidebarOverlay"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const app = document.querySelector('.app');
  const menuBtn = document.getElementById('menuHamburger');
  const overlay = document.getElementById('sidebarOverlay');

  if (menuBtn && app) {
    menuBtn.addEventListener('click', () => {
      app.classList.toggle('sidebar-open');
      menuBtn.setAttribute('aria-expanded', app.classList.contains('sidebar-open') ? 'true' : 'false');
    });
  }

  if (overlay && app) {
    overlay.addEventListener('click', () => {
      app.classList.remove('sidebar-open');
      menuBtn?.setAttribute('aria-expanded', 'false');
    });
  }

  if (window.innerWidth > 768 && app) {
    app.classList.remove('sidebar-open');
  }
});
</script>