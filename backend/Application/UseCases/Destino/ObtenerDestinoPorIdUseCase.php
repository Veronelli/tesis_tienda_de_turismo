<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Destino;

use TiendaTurismo\GestionDatos\Domain\Models\Destino;
use TiendaTurismo\GestionDatos\Domain\Repositories\DestinoRepositoryInterface;

final class ObtenerDestinoPorIdUseCase
{
    public function __construct(
        private readonly DestinoRepositoryInterface $destinos,
    ) {
    }

    public function execute(int $id): ?Destino
    {
        return $this->destinos->findById($id);
    }
}
