<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Consulta;

use TiendaTurismo\GestionDatos\Application\Input\ActualizarConsultaInput;
use TiendaTurismo\GestionDatos\Domain\Models\Consulta;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\ConsultaRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;

final class ActualizarConsultaUseCase
{
    public function __construct(
        private readonly ConsultaRepositoryInterface $consultas,
        private readonly ClienteRepositoryInterface $clientes,
        private readonly PaqueteRepositoryInterface $paquetes,
    ) {
    }

    public function execute(ActualizarConsultaInput $input): Consulta
    {
        $consulta = $this->consultas->findById($input->id);
        if ($consulta === null) {
            throw new \RuntimeException('Consulta no encontrada.');
        }

        $cliente = null;
        if ($input->clienteId !== null) {
            $cliente = $this->clientes->findById($input->clienteId);
            if ($cliente === null) {
                throw new \RuntimeException("El cliente con ID {$input->clienteId} no existe.");
            }
        }

        $paquete = null;
        if ($input->paqueteId !== null) {
            $paquete = $this->paquetes->findById($input->paqueteId);
            if ($paquete === null) {
                throw new \RuntimeException("El paquete con ID {$input->paqueteId} no existe.");
            }
        }

        $consulta->update(
            cliente: $cliente,
            paquete: $paquete,
            mensaje: $input->mensaje,
            estado: $input->estado,
            fechaConsulta: $input->fechaConsulta,
        );

        $this->consultas->update($consulta);

        return $consulta;
    }
}
