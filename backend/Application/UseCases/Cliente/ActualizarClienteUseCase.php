<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Cliente;

use TiendaTurismo\GestionDatos\Application\Input\ActualizarClienteInput;
use TiendaTurismo\GestionDatos\Domain\Models\Cliente;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;

final class ActualizarClienteUseCase
{
    public function __construct(
        private readonly ClienteRepositoryInterface $clientes,
    ) {
    }

    public function execute(ActualizarClienteInput $input): Cliente
    {
        $cliente = $this->clientes->findById($input->id);

        if ($cliente === null) {
            throw new \RuntimeException('Cliente no encontrado.');
        }

        $cliente->update(
            $input->nombre,
            $input->apellido,
            $input->email,
            $input->telefono,
            $input->dni,
            $input->ubicacion,
        );
        $this->clientes->update($cliente);

        return $cliente;
    }
}
