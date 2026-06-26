<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Interfaces\Http\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use TiendaTurismo\GestionDatos\Application\Services\ConsultaService;
use TiendaTurismo\GestionDatos\Infrastructure\Persistence\Doctrine\EntityManagerFactory;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\ClienteDoctrineRepository;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\ConsultaDoctrineRepository;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\PaqueteDoctrineRepository;
use TiendaTurismo\GestionDatos\Infrastructure\Security\JwtService;

final class ConsultaController
{
    private ConsultaService $consultaService;
    private JwtService $jwt;

    public function __construct(
        ?ConsultaService $consultaService = null,
        ?JwtService $jwt = null,
    ) {
        if ($consultaService === null) {
            $entityManager = EntityManagerFactory::createFromEnv();
            $this->consultaService = new ConsultaService(
                new ConsultaDoctrineRepository($entityManager),
                new ClienteDoctrineRepository($entityManager),
                new PaqueteDoctrineRepository($entityManager),
            );
        } else {
            $this->consultaService = $consultaService;
        }
        $this->jwt = $jwt ?? new JwtService();
    }

    public function listar(Request $request): JsonResponse
    {
        try {
            $this->requerirAutenticacion($request);

            $filtros = [];

            $estado = $request->query->get('estado');
            if ($estado !== null && $estado !== '') {
                $filtros['estado'] = $estado;
            }

            $calificacion = $request->query->get('calificacion');
            if ($calificacion !== null && $calificacion !== '') {
                $filtros['calificacion'] = strtolower(trim((string) $calificacion));
            }

            $cliente = $request->query->get('cliente');
            if ($cliente !== null && $cliente !== '') {
                $filtros['cliente'] = $cliente;
            }

            $paqueteId = $request->query->get('paquete_id');
            if ($paqueteId !== null && $paqueteId !== '') {
                $filtros['paquete_id'] = (int) $paqueteId;
            }

            $fechaDesde = $request->query->get('fecha_desde');
            if ($fechaDesde !== null && $fechaDesde !== '') {
                $filtros['fecha_desde'] = $fechaDesde;
            }

            $fechaHasta = $request->query->get('fecha_hasta');
            if ($fechaHasta !== null && $fechaHasta !== '') {
                $filtros['fecha_hasta'] = $fechaHasta;
            }

            $consultas = $this->consultaService->listar($filtros);

            return new JsonResponse($consultas);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 401);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function obtenerPorId(Request $request, array $params): JsonResponse
    {
        try {
            $this->requerirAutenticacion($request);

            $consulta = $this->consultaService->obtenerPorId((int) $params['id']);

            if ($consulta === null) {
                return new JsonResponse(['error' => 'Consulta no encontrada.'], 404);
            }

            return new JsonResponse($consulta);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 401);
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

            $consulta = $this->consultaService->crear($data);

            return new JsonResponse($consulta, 201);
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
        try {
            $this->requerirAutenticacion($request);

            $data = json_decode((string) $request->getContent(), true);

            if (!is_array($data)) {
                return new JsonResponse(['error' => 'Datos inválidos.'], 400);
            }

            $this->validarSinCalificacion($data);

            $data['id'] = (int) $params['id'];

            $consulta = $this->consultaService->actualizar($data);

            return new JsonResponse($consulta);
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
        if (!isset($data['paquete_id']) || !is_numeric($data['paquete_id'])) {
            throw new \InvalidArgumentException('El ID del paquete es requerido.');
        }

        if (!isset($data['mensaje']) || trim((string) $data['mensaje']) === '') {
            throw new \InvalidArgumentException('El mensaje es requerido.');
        }

        if (array_key_exists('calificacion', $data)) {
            throw new \InvalidArgumentException('La calificación del lead no puede ser enviada por el cliente.');
        }

        $tieneClienteId = isset($data['cliente_id']);
        $tieneDatosCliente = isset($data['nombre'], $data['apellido'], $data['email']);

        if (!$tieneClienteId && !$tieneDatosCliente) {
            throw new \InvalidArgumentException('Debe proporcionar un cliente_id o datos del cliente (nombre, apellido, email).');
        }
    }

    /** @param array<string, mixed> $data */
    private function validarSinCalificacion(array $data): void
    {
        if (array_key_exists('calificacion', $data)) {
            throw new \InvalidArgumentException('La calificación del lead no puede ser enviada por el cliente.');
        }
    }

    private function requerirAutenticacion(Request $request): void
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
    }

    public static function rutas(): RouteCollection
    {
        $routes = new RouteCollection();

        $routes->add('consultas.listar', new Route('/api/consultas', [
            '_controller' => self::class,
            '_action' => 'listar',
        ], methods: ['GET']));

        $routes->add('consultas.obtener', new Route('/api/consultas/{id}', [
            '_controller' => self::class,
            '_action' => 'obtenerPorId',
        ], methods: ['GET'], requirements: ['id' => '\d+']));

        $routes->add('consultas.crear', new Route('/api/consultas', [
            '_controller' => self::class,
            '_action' => 'crear',
        ], methods: ['POST']));

        $routes->add('consultas.actualizar', new Route('/api/consultas/{id}', [
            '_controller' => self::class,
            '_action' => 'actualizar',
        ], methods: ['PUT'], requirements: ['id' => '\d+']));

        return $routes;
    }
}
