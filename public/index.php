<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

require_once __DIR__ . '/../vendor/autoload.php';

\TiendaTurismo\GestionDatos\Infrastructure\Config\EnvLoader::load(__DIR__ . '/../.env');

$routes = require __DIR__ . '/../config/routes.php';

$request = Request::createFromGlobals();

$context = (new RequestContext())->fromRequest($request);

$matcher = new UrlMatcher($routes, $context);

try {
    $parameters = $matcher->match($request->getPathInfo());

    $controllerClass = $parameters['_controller'];
    $action = $parameters['_action'];

    $controller = new $controllerClass();
    $response = $controller->$action($request, $parameters);
} catch (ResourceNotFoundException) {
    $response = new JsonResponse(['error' => 'Ruta no encontrada'], 404);
} catch (\Throwable $e) {
    $response = new JsonResponse(['error' => $e->getMessage()], 500);
}

$response->send();
