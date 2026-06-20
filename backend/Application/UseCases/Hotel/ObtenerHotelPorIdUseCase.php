<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Hotel;

use TiendaTurismo\GestionDatos\Domain\Models\Hotel;
use TiendaTurismo\GestionDatos\Domain\Repositories\HotelRepositoryInterface;

final class ObtenerHotelPorIdUseCase
{
    public function __construct(
        private readonly HotelRepositoryInterface $hoteles,
    ) {
    }

    public function execute(int $id): ?Hotel
    {
        return $this->hoteles->findById($id);
    }
}
