<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Interfaces\Http\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use TiendaTurismo\GestionDatos\Application\UseCases\Usuario\LoginUseCase;
use TiendaTurismo\GestionDatos\Domain\Exceptions\CredencialesInvalidasException;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\UsuarioDoctrineRepository;
use TiendaTurismo\GestionDatos\Infrastructure\Security\JwtService;

final class LoginController
{
    private LoginUseCase $loginUseCase;

    public function __construct(?LoginUseCase $loginUseCase = null)
    {
        $this->loginUseCase = $loginUseCase ?? new LoginUseCase(
            new UsuarioDoctrineRepository(),
            new JwtService(),
        );
    }

    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email = (string) ($data['email'] ?? '');
        $password = (string) ($data['contrasena'] ?? $data['password'] ?? '');

        try {
            $token = $this->loginUseCase->execute($email, $password);

            return new JsonResponse(['token' => $token]);
        } catch (CredencialesInvalidasException) {
            return new JsonResponse(['error' => 'No Authorized'], 401);
        }
    }

    public static function rutas(): RouteCollection
    {
        $routes = new RouteCollection();

        $routes->add('usuarios.login', new Route('/api/usuarios/login', [
            '_controller' => self::class,
            '_action' => 'login',
        ], methods: ['POST']));

        return $routes;
    }
}
