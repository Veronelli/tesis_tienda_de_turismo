<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Interfaces\Http\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use TiendaTurismo\GestionDatos\Application\UseCases\Usuario\ObtenerUsuarioPorIdUseCase;
use TiendaTurismo\GestionDatos\Domain\Models\Usuario;
use TiendaTurismo\GestionDatos\Infrastructure\Security\JwtService;

abstract class BaseController
{
    protected JwtService $jwtService;
    private ObtenerUsuarioPorIdUseCase $obtenerUsuarioPorIdUseCase;

    /** @var list<callable(Request): ?JsonResponse> */
    private array $middleware;

    /**
     * @param list<callable(Request): ?JsonResponse> $middleware
     */
    public function __construct(?ObtenerUsuarioPorIdUseCase $obtenerUsuarioPorIdUseCase = null, ?JwtService $jwtService = null, array $middleware = [])
    {
        $this->obtenerUsuarioPorIdUseCase = $obtenerUsuarioPorIdUseCase ?? new ObtenerUsuarioPorIdUseCase(
            new \TiendaTurismo\GestionDatos\Infrastructure\Repositories\UsuarioDoctrineRepository(),
        );
        $this->jwtService = $jwtService ?? new JwtService();
        $this->middleware = $middleware;
    }

    protected function ejecutarMiddlewares(Request $request): ?JsonResponse
    {
        foreach ($this->middleware as $middleware) {
            $response = $middleware($request);

            if ($response instanceof JsonResponse) {
                return $response;
            }
        }

        return null;
    }

    protected function middlewareSoloLectura(Request $request): ?JsonResponse
    {
        return $this->validarRolVendedor($request, 'Los usuarios de tipo lector no pueden modificar destinos.');
    }

    protected function validarRolVendedor(Request $request, string $mensaje = 'No autorizado.'): ?JsonResponse
    {
        $header = $request->headers->get('Authorization', '');

        if (!str_starts_with($header, 'Bearer ')) {
            return new JsonResponse(['error' => 'No autorizado.'], 401);
        }

        $payload = $this->jwtService->decode(substr($header, 7));

        if ($payload === null) {
            return new JsonResponse(['error' => 'No autorizado.'], 401);
        }

        $usuarioId = (int) ($payload['sub'] ?? 0);
        if ($usuarioId <= 0) {
            return new JsonResponse(['error' => 'No autorizado.'], 401);
        }

        $usuario = $this->obtenerUsuarioPorIdUseCase->execute($usuarioId);

        if (!$usuario instanceof Usuario) {
            return new JsonResponse(['error' => 'No autorizado.'], 401);
        }

        if ($usuario->rol() !== 'vendedor') {
            return new JsonResponse(['error' => $mensaje], 403);
        }

        return null;
    }
}
