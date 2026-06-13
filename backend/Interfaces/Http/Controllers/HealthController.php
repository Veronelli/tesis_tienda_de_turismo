<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Interfaces\Http\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use TiendaTurismo\GestionDatos\Infrastructure\Config\EnvLoader;
use TiendaTurismo\GestionDatos\Infrastructure\Config\MariaDbConfig;
use Doctrine\DBAL\DriverManager;

final class HealthController
{
    public function check(): JsonResponse
    {
        $data = [
            'status' => 'ok',
            'timestamp' => date('c'),
            'php_version' => PHP_VERSION,
            'app_env' => $_ENV['APP_ENV'] ?? 'unknown',
        ];

        return new JsonResponse($data);
    }

    public function checkDb(): JsonResponse
    {
        try {
            EnvLoader::load(dirname(__DIR__, 4) . '/.env');
            $conn = DriverManager::getConnection(MariaDbConfig::connectionParams());
            $conn->getNativeConnection();

            $data = [
                'status' => 'ok',
                'timestamp' => date('c'),
                'database' => $_ENV['DB_DATABASE'] ?? 'unknown',
                'db_connected' => true,
            ];
        } catch (\Throwable $e) {
            $data = [
                'status' => 'error',
                'timestamp' => date('c'),
                'error' => $e->getMessage(),
            ];
        }

        return new JsonResponse($data);
    }

    public static function rutas(): RouteCollection
    {
        $routes = new RouteCollection();

        $routes->add('health.check', new Route('/api/health', [
            '_controller' => self::class,
            '_action' => 'check',
        ], methods: ['GET']));

        $routes->add('health.db', new Route('/api/health/db', [
            '_controller' => self::class,
            '_action' => 'checkDb',
        ], methods: ['GET']));

        return $routes;
    }
}
