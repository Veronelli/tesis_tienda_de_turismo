<?php

declare(strict_types=1);

use Symfony\Component\Routing\RouteCollection;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\AuthController;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\DestinoController;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\HealthController;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\LoginController;

$routes = new RouteCollection();

foreach ([HealthController::class, LoginController::class, AuthController::class, DestinoController::class] as $controller) {
    $routes->addCollection($controller::rutas());
}

return $routes;
