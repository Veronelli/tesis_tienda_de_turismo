<?php

declare(strict_types=1);

use Symfony\Component\Routing\RouteCollection;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\AuthController;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\ClienteController;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\DestinoController;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\HealthController;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\HotelController;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\LoginController;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\PaqueteController;

$routes = new RouteCollection();

foreach ([HealthController::class, LoginController::class, AuthController::class, DestinoController::class, HotelController::class, ClienteController::class, PaqueteController::class] as $controller) {
    $routes->addCollection($controller::rutas());
}

return $routes;
