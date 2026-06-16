<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\Services;

use TiendaTurismo\GestionDatos\Application\Input\ActualizarHotelInput;
use TiendaTurismo\GestionDatos\Application\Input\CrearHotelInput;
use TiendaTurismo\GestionDatos\Application\UseCases\Hotel\ActualizarHotelUseCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Hotel\CrearHotelUseCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Hotel\ListarHotelesUseCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Hotel\ObtenerHotelPorIdUseCase;
use TiendaTurismo\GestionDatos\Domain\Models\Hotel;
use TiendaTurismo\GestionDatos\Domain\Repositories\DestinoRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\HotelRepositoryInterface;

final class HotelService
{
    private CrearHotelUseCase $crearHotel;
    private ObtenerHotelPorIdUseCase $obtenerHotelPorId;
    private ListarHotelesUseCase $listarHoteles;
    private ActualizarHotelUseCase $actualizarHotel;

    public function __construct(
        HotelRepositoryInterface $hoteles,
        DestinoRepositoryInterface $destinos,
    ) {
        $this->crearHotel = new CrearHotelUseCase($hoteles, $destinos);
        $this->obtenerHotelPorId = new ObtenerHotelPorIdUseCase($hoteles);
        $this->listarHoteles = new ListarHotelesUseCase($hoteles);
        $this->actualizarHotel = new ActualizarHotelUseCase($hoteles, $destinos);
    }

    /** @param array{nombre:string,ubicacion:string,destino_id:int} $datos */
    public function crear(array $datos): array
    {
        $hotel = $this->crearHotel->execute(new CrearHotelInput(
            (string) $datos['nombre'],
            (string) $datos['ubicacion'],
            (int) $datos['destino_id'],
        ));
        return $this->serializarHotel($hotel);
    }

    public function obtenerPorId(int $id): ?array
    {
        $hotel = $this->obtenerHotelPorId->execute($id);
        if ($hotel === null) {
            return null;
        }
        return $this->serializarHotel($hotel);
    }

    /** @param array{id:int,nombre:string,ubicacion:string,destino_id:int} $datos */
    public function actualizar(array $datos): array
    {
        $hotel = $this->actualizarHotel->execute(new ActualizarHotelInput(
            (int) $datos['id'],
            (string) $datos['nombre'],
            (string) $datos['ubicacion'],
            (int) $datos['destino_id'],
        ));
        return $this->serializarHotel($hotel);
    }

    /** @return list<array<string, mixed>> */
    public function listar(): array
    {
        return array_map(
            fn (Hotel $hotel): array => $this->serializarHotel($hotel),
            $this->listarHoteles->execute(),
        );
    }

    /** @return array<string, mixed> */
    private function serializarHotel(Hotel $hotel): array
    {
        return $hotel->toArray();
    }
}
