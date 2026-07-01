<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Interfaces\Http\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use TiendaTurismo\GestionDatos\Application\Services\DestinoService;
use TiendaTurismo\GestionDatos\Application\UseCases\Usuario\ObtenerUsuarioPorIdUseCase;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\DestinoDoctrineRepository;

final class DestinoController extends BaseController
{
    private DestinoService $destinoService;
    public function __construct(
        ?DestinoService $destinoService = null,
        ?ObtenerUsuarioPorIdUseCase $obtenerUsuarioPorIdUseCase = null,
        array $middleware = [],
    )
    {
        parent::__construct($obtenerUsuarioPorIdUseCase, null, [
            [$this, 'middlewareSoloLectura'],
            ...$middleware,
        ]);

        $this->destinoService = $destinoService ?? new DestinoService(
            new DestinoDoctrineRepository(),
        );
    }

    public function listar(Request $request): JsonResponse
    {
        try {
            $destinos = $this->destinoService->listar();

            return new JsonResponse($destinos);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function crear(Request $request): JsonResponse
    {
        if ($response = $this->ejecutarMiddlewares($request)) {
            return $response;
        }

        $data = json_decode((string) $request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Datos invalidos.'], 400);
        }

        try {
            $destino = $this->destinoService->crear($data);

            return new JsonResponse($destino, 201);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }
    }

    public function actualizar(Request $request, array $params): JsonResponse
    {
        if ($response = $this->ejecutarMiddlewares($request)) {
            return $response;
        }

        $data = json_decode((string) $request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Datos invalidos.'], 400);
        }

        $data['id'] = (int) $params['id'];

        try {
            $destino = $this->destinoService->actualizar($data);

            return new JsonResponse($destino);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }
    }

    public static function rutas(): RouteCollection
    {
        $routes = new RouteCollection();

        $routes->add('destinos.listar', new Route('/api/destinos', [
            '_controller' => self::class,
            '_action' => 'listar',
        ], methods: ['GET']));

        $routes->add('destinos.crear', new Route('/api/destinos', [
            '_controller' => self::class,
            '_action' => 'crear',
        ], methods: ['POST']));

        $routes->add('destinos.actualizar', new Route('/api/destinos/{id}', [
            '_controller' => self::class,
            '_action' => 'actualizar',
        ], methods: ['PUT'], requirements: ['id' => '\d+']));

        return $routes;
    }
}
