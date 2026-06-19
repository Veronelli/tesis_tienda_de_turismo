<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Interfaces\Http\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use TiendaTurismo\GestionDatos\Application\Services\PaqueteService;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\HotelDoctrineRepository;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\PaqueteDoctrineRepository;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\UsuarioDoctrineRepository;
use TiendaTurismo\GestionDatos\Infrastructure\Security\JwtService;

final class PaqueteController
{
    private PaqueteService $paqueteService;
    private JwtService $jwt;

    public function __construct(?PaqueteService $paqueteService = null, ?JwtService $jwt = null)
    {
        $this->paqueteService = $paqueteService ?? new PaqueteService(
            new PaqueteDoctrineRepository(),
            new HotelDoctrineRepository(),
            new UsuarioDoctrineRepository(),
        );
        $this->jwt = $jwt ?? new JwtService();
    }

    public function listar(Request $request): JsonResponse
    {
        try {
            $filtros = [];

            $nombre = $request->query->get('nombre');
            if ($nombre !== null && $nombre !== '') {
                $filtros['nombre'] = $nombre;
            }

            $mesPartida = $request->query->get('mes_partida');
            if ($mesPartida !== null && $mesPartida !== '') {
                $filtros['mes_partida'] = (int) $mesPartida;
            }

            $destinoId = $request->query->get('destino_id');
            if ($destinoId !== null && $destinoId !== '') {
                $filtros['destino_id'] = (int) $destinoId;
            }

            $ordenPrecio = $request->query->get('orden_precio');
            if ($ordenPrecio !== null && in_array(strtolower($ordenPrecio), ['asc', 'desc'], true)) {
                $filtros['orden_precio'] = strtolower($ordenPrecio);
            }

            $paquetes = $this->paqueteService->listar($filtros);

            return new JsonResponse($paquetes);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function obtenerPorId(Request $request, array $params): JsonResponse
    {
        try {
            $paquete = $this->paqueteService->obtenerPorId((int) $params['id']);

            if ($paquete === null) {
                return new JsonResponse(['error' => 'Paquete no encontrado.'], 404);
            }

            return new JsonResponse($paquete);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function crear(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Datos inválidos.'], 400);
        }

        try {
            $this->validarDatosCrear($data);

            $usuarioId = $this->obtenerUsuarioDesdeToken($request);
            $data['usuario_responsable_id'] = $usuarioId;

            $paquete = $this->paqueteService->crear($data);

            return new JsonResponse($paquete, 201);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function actualizar(Request $request, array $params): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Datos inválidos.'], 400);
        }

        $data['id'] = (int) $params['id'];

        try {
            $this->validarDatosActualizar($data);

            $usuarioId = $this->obtenerUsuarioDesdeToken($request);
            $data['usuario_responsable_id'] = $usuarioId;

            $paquete = $this->paqueteService->actualizar($data);

            return new JsonResponse($paquete);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /** @param array<string, mixed> $data */
    private function validarDatosCrear(array $data): void
    {
        $this->validarCampoRequerido($data, 'nombre', 'El nombre es requerido.');
        $this->validarCampoRequerido($data, 'fecha_partida', 'La fecha de partida es requerida.');
        $this->validarCampoRequerido($data, 'precio', 'El precio es requerido.');

        if (!isset($data['precio']) || !is_numeric($data['precio'])) {
            throw new \InvalidArgumentException('El precio debe ser un valor numérico.');
        }

        if (!isset($data['hoteles_ids']) || !is_array($data['hoteles_ids']) || empty($data['hoteles_ids'])) {
            throw new \InvalidArgumentException('Debe seleccionar al menos un hotel.');
        }
    }

    /** @param array<string, mixed> $data */
    private function validarDatosActualizar(array $data): void
    {
        $this->validarDatosCrear($data);
    }

    /** @param array<string, mixed> $data */
    private function validarCampoRequerido(array $data, string $campo, string $mensaje): void
    {
        if (!isset($data[$campo]) || (is_string($data[$campo]) && trim($data[$campo]) === '')) {
            throw new \InvalidArgumentException($mensaje);
        }
    }

    private function obtenerUsuarioDesdeToken(Request $request): int
    {
        $header = $request->headers->get('Authorization', '');

        if (!str_starts_with($header, 'Bearer ')) {
            throw new \RuntimeException('Token de autenticación requerido.');
        }

        $token = substr($header, 7);
        $payload = $this->jwt->decode($token);

        if ($payload === null || !isset($payload['sub'])) {
            throw new \RuntimeException('Token inválido o expirado.');
        }

        return (int) $payload['sub'];
    }

    public static function rutas(): RouteCollection
    {
        $routes = new RouteCollection();

        $routes->add('paquetes.listar', new Route('/api/paquetes', [
            '_controller' => self::class,
            '_action' => 'listar',
        ], methods: ['GET']));

        $routes->add('paquetes.obtener', new Route('/api/paquetes/{id}', [
            '_controller' => self::class,
            '_action' => 'obtenerPorId',
        ], methods: ['GET'], requirements: ['id' => '\d+']));

        $routes->add('paquetes.crear', new Route('/api/paquetes', [
            '_controller' => self::class,
            '_action' => 'crear',
        ], methods: ['POST']));

        $routes->add('paquetes.actualizar', new Route('/api/paquetes/{id}', [
            '_controller' => self::class,
            '_action' => 'actualizar',
        ], methods: ['PUT'], requirements: ['id' => '\d+']));

        return $routes;
    }
}
