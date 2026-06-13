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
    .landing { display: flex; align-items: center; justify-content: center; min-height: 100vh; flex-direction: column; gap: 24px; }
    .landing h1 { font-size: 1.5rem; color: var(--text); }
    .landing p { color: var(--text-muted); font-size: 0.9rem; }
  </style>
</head>
<body>
<div class="landing">
  <div style="text-align:center">
    <h1>Tienda De Turismo</h1>
    <h3>Panel de administración — Bienvenido, <?= htmlspecialchars($usuario, ENT_QUOTES) ?></h3>
    <h4>Tu rol: <?= htmlspecialchars($rol, ENT_QUOTES) ?></h4>
    <br>
    <button class="btn btn-primary" onclick="logout()">Cerrar sesión</button>
    <br><br>
    <a href="login.html" style="font-size:0.85rem">Ir al login</a>
  </div>
</div>
<script src="js/auth.js"></script>
<script src="js/app.js"></script>
</body>
</html>
