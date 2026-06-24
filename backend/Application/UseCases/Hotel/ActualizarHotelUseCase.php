<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Hotel;

use TiendaTurismo\GestionDatos\Application\Input\ActualizarHotelInput;
use TiendaTurismo\GestionDatos\Domain\Models\Hotel;
use TiendaTurismo\GestionDatos\Domain\Repositories\DestinoRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\HotelRepositoryInterface;

final class ActualizarHotelUseCase
{
    public function __construct(
        private readonly HotelRepositoryInterface $hoteles,
        private readonly DestinoRepositoryInterface $destinos,
    ) {
    }

    public function execute(ActualizarHotelInput $input): Hotel
    {
        $hotel = $this->hoteles->findById($input->id);

        if ($hotel === null) {
            throw new \RuntimeException('Hotel no encontrado.');
        }

        $destino = $this->destinos->findById($input->destinoId);

        if ($destino === null) {
            throw new \RuntimeException('Destino no encontrado.');
        }

        $hotel->update($input->nombre, $input->ubicacion, $input->descripcion, $destino);
        $this->hoteles->update($hotel);

        return $hotel;
    }
}
