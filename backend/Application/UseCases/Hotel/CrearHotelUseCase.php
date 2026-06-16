<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Hotel;

use TiendaTurismo\GestionDatos\Application\Input\CrearHotelInput;
use TiendaTurismo\GestionDatos\Domain\Models\Destino;
use TiendaTurismo\GestionDatos\Domain\Models\Hotel;
use TiendaTurismo\GestionDatos\Domain\Repositories\DestinoRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\HotelRepositoryInterface;

final class CrearHotelUseCase
{
    public function __construct(
        private readonly HotelRepositoryInterface $hoteles,
        private readonly DestinoRepositoryInterface $destinos,
    ) {
    }

    public function execute(CrearHotelInput $input): Hotel
    {
        $destino = $this->destinos->findById($input->destinoId);

        if ($destino === null) {
            throw new \RuntimeException('Destino no encontrado.');
        }

        $hotel = new Hotel(
            $input->nombre,
            $input->ubicacion,
            $destino,
        );

        $this->hoteles->save($hotel);

        return $hotel;
    }
}
