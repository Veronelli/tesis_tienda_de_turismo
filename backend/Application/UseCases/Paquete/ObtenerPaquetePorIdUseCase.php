<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Paquete;

use TiendaTurismo\GestionDatos\Domain\Models\Paquete;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;

final class ObtenerPaquetePorIdUseCase
{
    public function __construct(
        private readonly PaqueteRepositoryInterface $paquetes,
    ) {
    }

    public function execute(int $id): ?Paquete
    {
        return $this->paquetes->findById($id);
    }
}
