<?php
/**
 * Base layout for dashboard pages
 *
 * Usage: require __DIR__ . '/components/layout.php';
 *
 * @param string $title Page title
 * @param string $currentPage Current page identifier for sidebar
 */

declare(strict_types=1);

use TiendaTurismo\GestionDatos\Infrastructure\Config\EnvLoader;
use TiendaTurismo\GestionDatos\Infrastructure\Security\JwtService;

require_once __DIR__ . '/../../../vendor/autoload.php';

EnvLoader::load(__DIR__ . '/../../../.env');

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
  <link rel="icon" type="image/x-icon" href="../../img/logo.png">

  <title><?= htmlspecialchars($title ?? 'Dashboard', ENT_QUOTES) ?> - Tienda De Turismo Admin</title>
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
      width: 50px;
      height: 50px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #1a1a2e;
      font-size: 0.42rem;
      font-weight: 700;
      text-align: center;
      line-height: 0.85;
    }

    .main {
      padding-top: 52px;
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
      <button id="menuHamburger" class="dashboard-topbar__menu" type="button" aria-label="Desplegar u ocultar menú" aria-expanded="false" onclick="toggleDashboardSidebar()">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <h1 class="dashboard-topbar__title">Tienda De Turismo Admin</h1>
    </div>
      <img src="../img/logo.png" alt=""class="dashboard-topbar__logo">
  </header>
  <?php require __DIR__ . '/sidebar.php'; ?>
  <main class="main">
    <div class="content">
