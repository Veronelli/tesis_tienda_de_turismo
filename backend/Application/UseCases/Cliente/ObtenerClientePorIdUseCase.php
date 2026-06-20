<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Cliente;

use TiendaTurismo\GestionDatos\Domain\Models\Cliente;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;

final class ObtenerClientePorIdUseCase
{
    public function __construct(
        private readonly ClienteRepositoryInterface $clientes,
    ) {
    }

    public function execute(int $id): ?Cliente
    {
        return $this->clientes->findById($id);
    }
}
