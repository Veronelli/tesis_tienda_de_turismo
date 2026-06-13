<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Interfaces\Http\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use TiendaTurismo\GestionDatos\Infrastructure\Security\JwtService;

final class AuthController
{
    private JwtService $jwt;

    public function __construct(?JwtService $jwt = null)
    {
        $this->jwt = $jwt ?? new JwtService();
    }

    public function verificar(Request $request): JsonResponse
    {
        $header = $request->headers->get('Authorization', '');

        if (!str_starts_with($header, 'Bearer ')) {
            return new JsonResponse(['valido' => false], 401);
        }

        $token = substr($header, 7);
        $payload = $this->jwt->decode($token);

        if ($payload === null) {
            return new JsonResponse(['valido' => false], 401);
        }

        return new JsonResponse([
            'valido' => true,
            'usuario' => [
                'id' => $payload['sub'],
                'email' => $payload['email'],
                'rol' => $payload['rol'],
            ],
        ]);
    }

    public static function rutas(): RouteCollection
    {
        $routes = new RouteCollection();

        $routes->add('auth.verificar', new Route('/api/auth/verificar', [
            '_controller' => self::class,
            '_action' => 'verificar',
        ], methods: ['GET']));

        return $routes;
    }
}
