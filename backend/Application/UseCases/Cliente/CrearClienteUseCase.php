<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Cliente;

use TiendaTurismo\GestionDatos\Application\Input\CrearClienteInput;
use TiendaTurismo\GestionDatos\Domain\Models\Cliente;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;

final class CrearClienteUseCase
{
    public function __construct(
        private readonly ClienteRepositoryInterface $clientes,
    ) {
    }

    public function execute(CrearClienteInput $input): Cliente
    {
        $cliente = new Cliente(
            $input->nombre,
            $input->apellido,
            $input->email,
            $input->telefono,
            $input->dni,
            $input->ubicacion,
        );

        $this->clientes->save($cliente);

        return $cliente;
    }
}
