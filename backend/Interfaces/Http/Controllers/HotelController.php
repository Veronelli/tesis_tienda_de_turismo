<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Interfaces\Http\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use TiendaTurismo\GestionDatos\Application\Services\HotelService;
use TiendaTurismo\GestionDatos\Application\UseCases\Usuario\ObtenerUsuarioPorIdUseCase;
use TiendaTurismo\GestionDatos\Infrastructure\Persistence\Doctrine\EntityManagerFactory;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\DestinoDoctrineRepository;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\HotelDoctrineRepository;

final class HotelController extends BaseController
{
    private HotelService $hotelService;

    public function __construct(
        ?HotelService $hotelService = null,
        ?ObtenerUsuarioPorIdUseCase $obtenerUsuarioPorIdUseCase = null,
        ?EntityManagerInterface $em = null,
    )
    {
        parent::__construct($obtenerUsuarioPorIdUseCase, null, [
            [$this, 'middlewareSoloLectura'],
        ]);

        $em ??= EntityManagerFactory::createFromEnv();
        $this->hotelService = $hotelService ?? new HotelService(
            new HotelDoctrineRepository($em),
            new DestinoDoctrineRepository($em),
        );
    }

    public function listar(Request $request): JsonResponse
    {
        try {
            $hoteles = $this->hotelService->listar();
            return new JsonResponse($hoteles);
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
            $hotel = $this->hotelService->crear($data);
            return new JsonResponse($hotel, 201);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
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
            $hotel = $this->hotelService->actualizar($data);
            return new JsonResponse($hotel);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }
    }

    public static function rutas(): RouteCollection
    {
        $routes = new RouteCollection();

        $routes->add('hoteles.listar', new Route('/api/hoteles', [
            '_controller' => self::class,
            '_action' => 'listar',
        ], methods: ['GET']));

        $routes->add('hoteles.crear', new Route('/api/hoteles', [
            '_controller' => self::class,
            '_action' => 'crear',
        ], methods: ['POST']));

        $routes->add('hoteles.actualizar', new Route('/api/hoteles/{id}', [
            '_controller' => self::class,
            '_action' => 'actualizar',
        ], methods: ['PUT'], requirements: ['id' => '\d+']));

        return $routes;
    }
}
