<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Consulta;

use TiendaTurismo\GestionDatos\Application\Input\CrearConsultaInput;
use TiendaTurismo\GestionDatos\Domain\Models\Cliente;
use TiendaTurismo\GestionDatos\Domain\Models\Consulta;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\ConsultaRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;

final class CrearConsultaUseCase
{
    public function __construct(
        private readonly ConsultaRepositoryInterface $consultas,
        private readonly ClienteRepositoryInterface $clientes,
        private readonly PaqueteRepositoryInterface $paquetes,
    ) {
    }

    public function execute(CrearConsultaInput $input): Consulta
    {
        $paquete = $this->paquetes->findById($input->paqueteId);
        if ($paquete === null) {
            throw new \RuntimeException("El paquete con ID {$input->paqueteId} no existe.");
        }

        $cliente = $this->resolveCliente($input);

        $consulta = new Consulta(
            cliente: $cliente,
            paquete: $paquete,
            mensaje: $input->mensaje,
            calificacion: $input->calificacion,
            fechaConsulta: $input->fechaConsulta,
        );

        $this->consultas->save($consulta);

        return $consulta;
    }

    private function resolveCliente(CrearConsultaInput $input): Cliente
    {
        if ($input->clienteId !== null) {
            $cliente = $this->clientes->findById($input->clienteId);
            if ($cliente === null) {
                throw new \RuntimeException("El cliente con ID {$input->clienteId} no existe.");
            }
            return $cliente;
        }

        if ($input->datosCliente === null) {
            throw new \InvalidArgumentException('Debe proporcionar un cliente_id o datos completos del cliente.');
        }

        $this->validarDatosCliente($input->datosCliente);

        $email = $input->datosCliente['email'];
        $existente = $this->clientes->findByEmail($email);
        if ($existente !== null) {
            return $existente;
        }

        $cliente = new Cliente(
            nombre: $input->datosCliente['nombre'],
            apellido: $input->datosCliente['apellido'],
            email: $input->datosCliente['email'],
            telefono: $input->datosCliente['telefono'],
            dni: $input->datosCliente['dni'],
            ubicacion: $input->datosCliente['ubicacion'],
        );

        $this->clientes->save($cliente);

        return $cliente;
    }

    /** @param array<string, string> $datos */
    private function validarDatosCliente(array $datos): void
    {
        $requeridos = ['nombre', 'apellido', 'email', 'telefono', 'dni', 'ubicacion'];
        foreach ($requeridos as $campo) {
            if (!isset($datos[$campo]) || trim((string) $datos[$campo]) === '') {
                throw new \InvalidArgumentException("El campo {$campo} del cliente es obligatorio.");
            }
        }
    }
}
