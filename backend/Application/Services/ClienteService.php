<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\Services;

use TiendaTurismo\GestionDatos\Application\Input\ActualizarClienteInput;
use TiendaTurismo\GestionDatos\Application\Input\CrearClienteInput;
use TiendaTurismo\GestionDatos\Application\UseCases\Cliente\ActualizarClienteUseCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Cliente\CrearClienteUseCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Cliente\ListarClientesUseCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Cliente\ObtenerClientePorIdUseCase;
use TiendaTurismo\GestionDatos\Domain\Models\Cliente;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;

class ClienteService
{
    private CrearClienteUseCase $crearCliente;
    private ObtenerClientePorIdUseCase $obtenerClientePorId;
    private ListarClientesUseCase $listarClientes;
    private ActualizarClienteUseCase $actualizarCliente;

    public function __construct(
        ClienteRepositoryInterface $clientes,
    ) {
        $this->crearCliente = new CrearClienteUseCase($clientes);
        $this->obtenerClientePorId = new ObtenerClientePorIdUseCase($clientes);
        $this->listarClientes = new ListarClientesUseCase($clientes);
        $this->actualizarCliente = new ActualizarClienteUseCase($clientes);
    }

    /** @param array{nombre:string,apellido:string,email:string,telefono:string,dni:string,ubicacion:string} $datos */
    public function crear(array $datos): array
    {
        $cliente = $this->crearCliente->execute(new CrearClienteInput(
            (string) $datos['nombre'],
            (string) $datos['apellido'],
            (string) $datos['email'],
            (string) $datos['telefono'],
            (string) $datos['dni'],
            (string) $datos['ubicacion'],
        ));
        return $this->serializarCliente($cliente);
    }

    public function obtenerPorId(int $id): ?array
    {
        $cliente = $this->obtenerClientePorId->execute($id);
        if ($cliente === null) {
            return null;
        }
        return $this->serializarCliente($cliente);
    }

    /** @param array{id:int,nombre:string,apellido:string,email:string,telefono:string,dni:string,ubicacion:string} $datos */
    public function actualizar(array $datos): array
    {
        $cliente = $this->actualizarCliente->execute(new ActualizarClienteInput(
            (int) $datos['id'],
            (string) $datos['nombre'],
            (string) $datos['apellido'],
            (string) $datos['email'],
            (string) $datos['telefono'],
            (string) $datos['dni'],
            (string) $datos['ubicacion'],
        ));
        return $this->serializarCliente($cliente);
    }

    /** @return list<array<string, mixed>> */
    public function listar(): array
    {
        return array_map(
            fn (Cliente $cliente): array => $this->serializarCliente($cliente),
            $this->listarClientes->execute(),
        );
    }

    /** @return array<string, mixed> */
    private function serializarCliente(Cliente $cliente): array
    {
        return $cliente->toArray();
    }
}
