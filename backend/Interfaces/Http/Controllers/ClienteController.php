<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Interfaces\Http\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use TiendaTurismo\GestionDatos\Application\Services\ClienteService;
use TiendaTurismo\GestionDatos\Application\UseCases\Usuario\ObtenerUsuarioPorIdUseCase;
use TiendaTurismo\GestionDatos\Domain\Exceptions\DuplicadoException;
use TiendaTurismo\GestionDatos\Infrastructure\Persistence\Doctrine\EntityManagerFactory;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\ClienteDoctrineRepository;

final class ClienteController extends BaseController
{
    private ClienteService $clienteService;

    public function __construct(
        ?ClienteService $clienteService = null,
        ?ObtenerUsuarioPorIdUseCase $obtenerUsuarioPorIdUseCase = null,
        ?EntityManagerInterface $em = null,
    ) {
        parent::__construct($obtenerUsuarioPorIdUseCase, null, [
            [$this, 'middlewareSoloLecturaClientes'],
        ]);

        $em ??= EntityManagerFactory::createFromEnv();
        $this->clienteService = $clienteService ?? new ClienteService(
            new ClienteDoctrineRepository($em),
        );
    }

    public function listar(Request $request): JsonResponse
    {
        try {
            $clientes = $this->clienteService->listar();
            return new JsonResponse($clientes);
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
            $cliente = $this->clienteService->crear($data);
            return new JsonResponse($cliente, 201);
        } catch (DuplicadoException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 409);
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
            $cliente = $this->clienteService->actualizar($data);
            return new JsonResponse($cliente);
        } catch (DuplicadoException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 409);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }
    }

    public static function rutas(): RouteCollection
    {
        $routes = new RouteCollection();

        $routes->add('clientes.listar', new Route('/api/clientes', [
            '_controller' => self::class,
            '_action' => 'listar',
        ], methods: ['GET']));

        $routes->add('clientes.crear', new Route('/api/clientes', [
            '_controller' => self::class,
            '_action' => 'crear',
        ], methods: ['POST']));

        $routes->add('clientes.actualizar', new Route('/api/clientes/{id}', [
            '_controller' => self::class,
            '_action' => 'actualizar',
        ], methods: ['PUT'], requirements: ['id' => '\\d+']));

        return $routes;
    }

    protected function middlewareSoloLecturaClientes(Request $request): ?JsonResponse
    {
        return $this->validarRolVendedor($request, 'Los usuarios de tipo lector no pueden modificar clientes.');
    }
}
