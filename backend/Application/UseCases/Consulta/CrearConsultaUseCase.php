<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Consulta;

use TiendaTurismo\GestionDatos\Application\Input\CrearConsultaInput;
use TiendaTurismo\GestionDatos\Application\AI\Contracts\ProspectoCalificadorInterface;
use TiendaTurismo\GestionDatos\Domain\Exceptions\DuplicadoException;
use TiendaTurismo\GestionDatos\Domain\Models\Cliente;
use TiendaTurismo\GestionDatos\Domain\Models\Consulta;
use TiendaTurismo\GestionDatos\Domain\Models\Paquete;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\ConsultaRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;

final class CrearConsultaUseCase
{
    public function __construct(
        private readonly ConsultaRepositoryInterface $consultas,
        private readonly ClienteRepositoryInterface $clientes,
        private readonly PaqueteRepositoryInterface $paquetes,
        private readonly ProspectoCalificadorInterface $enviarProspecto,
    ) {
    }

    public function execute(CrearConsultaInput $input): Consulta
    {
        $paquete = $this->paquetes->findById($input->paqueteId);
        if ($paquete === null) {
            throw new \RuntimeException("El paquete con ID {$input->paqueteId} no existe.");
        }

        $cliente = $this->resolveCliente($input);
        $prospecto = $this->enviarProspecto->execute($input->mensaje, $this->buildContext($paquete));

        $consulta = new Consulta(
            cliente: $cliente,
            paquete: $paquete,
            mensaje: $input->mensaje,
            calificacion: $prospecto['calificacion'],
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

        $email = Cliente::normalizarEmail($input->datosCliente['email']);
        $dni = Cliente::normalizarDni($input->datosCliente['dni']);

        $porEmail = $this->clientes->findByEmail($email);
        $porDni = $this->clientes->findByDni($dni);

        if ($porEmail !== null && $porDni !== null) {
            if ($porEmail->id() !== null && $porDni->id() !== null && $porEmail->id() === $porDni->id()) {
                return $porEmail;
            }
            throw new DuplicadoException('El email y el DNI pertenecen a clientes distintos.');
        }

        if ($porEmail !== null) {
            return $porEmail;
        }

        if ($porDni !== null) {
            return $porDni;
        }

        $cliente = new Cliente(
            nombre: $input->datosCliente['nombre'],
            apellido: $input->datosCliente['apellido'],
            email: $email,
            telefono: $input->datosCliente['telefono'],
            dni: $dni,
            ubicacion: $input->datosCliente['ubicacion'],
        );

        $this->clientes->save($cliente);

        return $cliente;
    }

    private function buildContext(Paquete $paquete): string
    {
        $hoteles = [];
        $destinos = [];

        foreach ($paquete->hoteles() as $hotel) {
            $hoteles[] = $hotel->nombre();

            $destino = $hotel->destino();
            $destinos[] = implode(', ', array_filter([
                $destino->ciudad(),
                $destino->estadoProvincia(),
                $destino->pais(),
            ]));
        }

        $partes = [
            'Nombre: ' . $paquete->nombre(),
            'Descripcion: ' . ($paquete->descripcion() ?? ''),
            'Fecha De Ida: ' . $paquete->fechaPartida()->format('Y-m-d'),
            'Fecha De Vuelta: ' . ($paquete->fechaVuelta()?->format('Y-m-d') ?? ''),
            'Precio: ' . $paquete->precio(),
            'Desayuno: ' . ($paquete->desayuno() ? 'Si' : 'No'),
            'Pileta: ' . ($paquete->pileta() ? 'Si' : 'No'),
            'Heteles: ' . ($hoteles !== [] ? implode(', ', array_values(array_unique($hoteles))) : ''),
            'Destino: ' . ($destinos !== [] ? implode(', ', array_values(array_unique($destinos))) : ''),
        ];

        return implode("\n", $partes);
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
