<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\Services;

use TiendaTurismo\GestionDatos\Application\Input\ActualizarPaqueteInput;
use TiendaTurismo\GestionDatos\Application\Input\CrearPaqueteInput;
use TiendaTurismo\GestionDatos\Application\UseCases\Paquete\ActualizarPaqueteUseCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Paquete\CrearPaqueteUseCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Paquete\ListarPaquetesUseCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Paquete\ObtenerPaquetePorIdUseCase;
use TiendaTurismo\GestionDatos\Domain\Models\Paquete;
use TiendaTurismo\GestionDatos\Domain\Repositories\HotelRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\UsuarioRepositoryInterface;

class PaqueteService
{
    private CrearPaqueteUseCase $crearPaquete;
    private ObtenerPaquetePorIdUseCase $obtenerPaquetePorId;
    private ListarPaquetesUseCase $listarPaquetes;
    private ActualizarPaqueteUseCase $actualizarPaquete;

    public function __construct(
        PaqueteRepositoryInterface $paquetes,
        HotelRepositoryInterface $hoteles,
        UsuarioRepositoryInterface $usuarios,
    ) {
        $this->crearPaquete = new CrearPaqueteUseCase($paquetes, $hoteles, $usuarios);
        $this->obtenerPaquetePorId = new ObtenerPaquetePorIdUseCase($paquetes);
        $this->listarPaquetes = new ListarPaquetesUseCase($paquetes);
        $this->actualizarPaquete = new ActualizarPaqueteUseCase($paquetes, $hoteles, $usuarios);
    }

    /** @param array{nombre:string,descripcion?:string,imagen_principal?:string|null,fecha_partida:string,fecha_vuelta?:string,precio:string|float,disponible:bool,usuario_responsable_id:int,hoteles_ids:list<int>} $datos */
    public function crear(array $datos): array
    {
        $paquete = $this->crearPaquete->execute(new CrearPaqueteInput(
            nombre: (string) $datos['nombre'],
            descripcion: isset($datos['descripcion']) ? (string) $datos['descripcion'] : null,
            fechaPartida: new \DateTimeImmutable((string) $datos['fecha_partida']),
            fechaVuelta: isset($datos['fecha_vuelta']) ? new \DateTimeImmutable((string) $datos['fecha_vuelta']) : null,
            precio: (string) $datos['precio'],
            disponible: (bool) ($datos['disponible'] ?? true),
            usuarioResponsableId: (int) $datos['usuario_responsable_id'],
            hotelesIds: $this->parseHotelesIds($datos['hoteles_ids'] ?? []),
            imagenPrincipal: $datos['imagen_principal'] ?? null,
        ));

        return $this->serializarPaquete($paquete);
    }

    public function obtenerPorId(int $id): ?array
    {
        $paquete = $this->obtenerPaquetePorId->execute($id);

        if ($paquete === null) {
            return null;
        }

        return $this->serializarPaquete($paquete);
    }

    /** @param array{id:int,nombre:string,descripcion?:string,imagen_principal?:string|null,fecha_partida:string,fecha_vuelta?:string,precio:string|float,disponible:bool,usuario_responsable_id:int,hoteles_ids:list<int>} $datos */
    public function actualizar(array $datos): array
    {
        $paquete = $this->actualizarPaquete->execute(new ActualizarPaqueteInput(
            id: (int) $datos['id'],
            nombre: (string) $datos['nombre'],
            descripcion: isset($datos['descripcion']) ? (string) $datos['descripcion'] : null,
            fechaPartida: new \DateTimeImmutable((string) $datos['fecha_partida']),
            fechaVuelta: isset($datos['fecha_vuelta']) ? new \DateTimeImmutable((string) $datos['fecha_vuelta']) : null,
            precio: (string) $datos['precio'],
            disponible: (bool) ($datos['disponible'] ?? true),
            usuarioResponsableId: (int) $datos['usuario_responsable_id'],
            hotelesIds: $this->parseHotelesIds($datos['hoteles_ids'] ?? []),
            imagenPrincipal: $datos['imagen_principal'] ?? null,
        ));

        return $this->serializarPaquete($paquete);
    }

    /** @param array<string, mixed> $filtros */
    public function listar(array $filtros = []): array
    {
        return array_map(
            fn (Paquete $paquete): array => $this->serializarPaquete($paquete),
            $this->listarPaquetes->execute($filtros),
        );
    }

    /** @return array<string, mixed> */
    private function serializarPaquete(Paquete $paquete): array
    {
        return $paquete->toArray();
    }

    /** @param mixed $ids */
    private function parseHotelesIds($ids): array
    {
        if (!is_array($ids)) {
            return [];
        }

        return array_map('intval', $ids);
    }
}
