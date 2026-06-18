<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Cliente;

use TiendaTurismo\GestionDatos\Domain\Models\Cliente;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;

final class ListarClientesUseCase
{
    public function __construct(
        private readonly ClienteRepositoryInterface $clientes,
    ) {
    }

    /** @return list<Cliente> */
    public function execute(): array
    {
        return $this->clientes->findAll();
    }
}
