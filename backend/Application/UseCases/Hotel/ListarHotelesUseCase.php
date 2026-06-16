<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Hotel;

use TiendaTurismo\GestionDatos\Domain\Models\Hotel;
use TiendaTurismo\GestionDatos\Domain\Repositories\HotelRepositoryInterface;

final class ListarHotelesUseCase
{
    public function __construct(
        private readonly HotelRepositoryInterface $hoteles,
    ) {
    }

    /** @return list<Hotel> */
    public function execute(): array
    {
        return $this->hoteles->findAll();
    }
}
