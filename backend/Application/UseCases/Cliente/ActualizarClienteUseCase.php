<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Cliente;

use TiendaTurismo\GestionDatos\Application\Input\ActualizarClienteInput;
use TiendaTurismo\GestionDatos\Domain\Exceptions\DuplicadoException;
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

        $email = Cliente::normalizarEmail($input->email);
        $dni = Cliente::normalizarDni($input->dni);

        $porEmail = $this->clientes->findByEmail($email);
        $porDni = $this->clientes->findByDni($dni);

        if ($porEmail !== null && $porEmail->id() !== $cliente->id()) {
            throw new DuplicadoException('Ya existe un cliente con ese email.');
        }

        if ($porDni !== null && $porDni->id() !== $cliente->id()) {
            throw new DuplicadoException('Ya existe un cliente con ese DNI.');
        }

        if ($porEmail !== null && $porDni !== null && $porEmail->id() !== $porDni->id()) {
            throw new DuplicadoException('El email y el DNI pertenecen a clientes distintos.');
        }

        $cliente->update(
            $input->nombre,
            $input->apellido,
            $email,
            $input->telefono,
            $dni,
            $input->ubicacion,
        );
        $this->clientes->update($cliente);

        return $cliente;
    }
}
