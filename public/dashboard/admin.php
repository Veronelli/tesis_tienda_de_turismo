<?php

declare(strict_types=1);

use TiendaTurismo\GestionDatos\Infrastructure\Config\EnvLoader;
use TiendaTurismo\GestionDatos\Infrastructure\Security\JwtService;

require_once __DIR__ . '/../../vendor/autoload.php';

EnvLoader::load(__DIR__ . '/../../.env');

$token = $_COOKIE['tdt_token'] ?? '';
$payload = $token !== '' ? (new JwtService())->decode($token) : null;

if ($payload === null) {
    header('Location: login.html');
    exit;
}

$usuario = $payload['email'] ?? 'Usuario';
$rol = $payload['rol'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tienda De Turismo Admin</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .dashboard-topbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 52px;
      z-index: 120;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 14px;
      background: #fbf3e5;
      border-bottom: 1px solid rgba(26, 26, 46, 0.06);
    }

    .dashboard-topbar__left {
      display: flex;
      align-items: center;
      gap: 18px;
      min-width: 0;
    }

    .dashboard-topbar__menu {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 28px;
      height: 28px;
      border: 0;
      border-radius: 4px;
      background: transparent;
      color: #1a1a2e;
      cursor: pointer;
    }

    .dashboard-topbar__menu:hover {
      background: rgba(26, 26, 46, 0.06);
    }

    .dashboard-topbar__title {
      margin: 0;
      color: #1a1a2e;
      font-size: 1.25rem;
      font-weight: 500;
      line-height: 1;
      white-space: nowrap;
    }

    .dashboard-topbar__logo {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: radial-gradient(circle at 35% 35%, #35b7ff 0 20%, #8fd14f 21% 42%, #ffb000 43% 68%, #f98a1f 69% 100%);
      color: #1a1a2e;
      font-size: 0.42rem;
      font-weight: 700;
      text-align: center;
      line-height: 0.85;
    }

    .main {
      padding-top: 52px;
    }

    .app.sidebar-collapsed .md3-sidebar {
      transform: translateX(-100%);
    }

    .app.sidebar-collapsed .md3-sidebar ~ .main {
      margin-left: 0;
    }

    @media (max-width: 420px) {
      .dashboard-topbar__title { font-size: 1.05rem; }
      .dashboard-topbar__left { gap: 12px; }
    }
  </style>
</head>
<body>
<div class="app">
  <header class="dashboard-topbar">
    <div class="dashboard-topbar__left">
      <button id="menuHamburger" class="dashboard-topbar__menu" type="button" aria-label="Desplegar u ocultar menú" aria-expanded="true">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <h1 class="dashboard-topbar__title">Tienda De Turismo Admin</h1>
    </div>
    <div class="dashboard-topbar__logo" aria-hidden="true">Tienda<br>Turismo</div>
  </header>
  <?php require __DIR__ . '/components/sidebar.php'; ?>
  <main class="main">
    <div class="content" id="section-content">
      <h2 id="section-title" style="margin-bottom:16px">Consultas</h2>
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
          <div class="inquiry-grid" id="consultas-list"></div>
        </div>
      </div>
    </div>
  </main>
</div>

<script src="js/auth.js"></script>
<script src="js/app.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const app = document.querySelector('.app');
  const sidebar = document.querySelector('.md3-sidebar');
  const navLinks = document.querySelectorAll('.md3-sidebar__link[data-section]');
  const sectionTitle = document.getElementById('section-title');
  const menuBtn = document.getElementById('menuHamburger');
  const overlay = document.getElementById('sidebarOverlay');

  const sections = {
    consultas: { title: 'Consultas' },
    paquetes:  { title: 'Paquetes' },
    clientes:  { title: 'Clientes' },
    hoteles:   { title: 'Hoteles' },
    destinos:  { title: 'Destinos' },
  };

  function activateSection(name) {
    navLinks.forEach(l => l.classList.toggle('md3-sidebar__link--active', l.dataset.section === name));
    sectionTitle.textContent = (sections[name] || {}).title || name;
    if (window.innerWidth <= 768 && app) {
      app.classList.add('sidebar-collapsed');
      menuBtn?.setAttribute('aria-expanded', 'false');
    }
  }

  navLinks.forEach(link => {
    link.addEventListener('click', () => activateSection(link.dataset.section));
  });

  if (menuBtn && sidebar) {
    menuBtn.addEventListener('click', () => {
      app.classList.toggle('sidebar-collapsed');
      menuBtn.setAttribute('aria-expanded', app.classList.contains('sidebar-collapsed') ? 'false' : 'true');
    });
  }

  if (overlay && app) {
    overlay.addEventListener('click', () => {
      app.classList.add('sidebar-collapsed');
      menuBtn?.setAttribute('aria-expanded', 'false');
    });
  }
});
</script>
</body>
</html>
