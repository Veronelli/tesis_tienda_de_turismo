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

// When using mod_rewrite from a subdirectory, getBaseUrl() returns empty
// because SCRIPT_NAME (/tienda/.../public/index.php) and REQUEST_URI (/tienda/.../api/health)
// don't match. We detect the base path from SCRIPT_NAME instead.
$baseUrl = $request->getBaseUrl();
if ($baseUrl === '' || $baseUrl === '/') {
    $scriptName = $request->server->get('SCRIPT_NAME', '');
    $baseUrl = dirname(dirname($scriptName));
}

if ($baseUrl !== '' && $baseUrl !== '/') {
    $request->server->set('REQUEST_URI', substr(
        $request->getRequestUri(),
        strlen($baseUrl)
    ));
    $request->initialize(
        $request->query->all(),
        $request->request->all(),
        $request->attributes->all(),
        $request->cookies->all(),
        $request->files->all(),
        $request->server->all(),
        $request->getContent(),
    );
}

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
