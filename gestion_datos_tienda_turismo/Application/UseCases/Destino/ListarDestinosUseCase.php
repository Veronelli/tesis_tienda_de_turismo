<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Destino;

use TiendaTurismo\GestionDatos\Domain\Models\Destino;
use TiendaTurismo\GestionDatos\Domain\Repositories\DestinoRepositoryInterface;

final class ListarDestinosUseCase
{
    public function __construct(
        private readonly DestinoRepositoryInterface $destinos,
    ) {
    }

    /** @return list<Destino> */
    public function execute(): array
    {
        return $this->destinos->findAll();
    }
}
