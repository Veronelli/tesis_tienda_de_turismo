<?php

declare(strict_types=1);

use Symfony\Component\Routing\RouteCollection;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\LoginController;

$routes = new RouteCollection();

foreach ([LoginController::class] as $controller) {
    $routes->addCollection($controller::rutas());
}

return $routes;
