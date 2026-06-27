<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Cliente;

use TiendaTurismo\GestionDatos\Application\Input\CrearClienteInput;
use TiendaTurismo\GestionDatos\Domain\Exceptions\DuplicadoException;
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
        $email = Cliente::normalizarEmail($input->email);
        $dni = Cliente::normalizarDni($input->dni);

        $porEmail = $this->clientes->findByEmail($email);
        $porDni = $this->clientes->findByDni($dni);

        if ($porEmail !== null && $porDni !== null && $porEmail->id() !== $porDni->id()) {
            throw new DuplicadoException('El email y el DNI pertenecen a clientes distintos.');
        }

        if ($porEmail !== null) {
            throw new DuplicadoException('Ya existe un cliente con ese email.');
        }

        if ($porDni !== null) {
            throw new DuplicadoException('Ya existe un cliente con ese DNI.');
        }

        $cliente = new Cliente(
            $input->nombre,
            $input->apellido,
            $email,
            $input->telefono,
            $dni,
            $input->ubicacion,
        );

        $this->clientes->save($cliente);

        return $cliente;
    }
}
