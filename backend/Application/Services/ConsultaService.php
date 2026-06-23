<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\Services;

use TiendaTurismo\GestionDatos\Application\Input\ActualizarConsultaInput;
use TiendaTurismo\GestionDatos\Application\Input\CrearConsultaInput;
use TiendaTurismo\GestionDatos\Application\UseCases\Consulta\ActualizarConsultaUseCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Consulta\CrearConsultaUseCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Consulta\EliminarConsultaUseCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Consulta\ListarConsultasUseCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Consulta\ObtenerConsultaPorIdUseCase;
use TiendaTurismo\GestionDatos\Domain\Models\Consulta;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\ConsultaRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;

class ConsultaService
{
    private CrearConsultaUseCase $crearConsulta;
    private ObtenerConsultaPorIdUseCase $obtenerConsultaPorId;
    private ListarConsultasUseCase $listarConsultas;
    private ActualizarConsultaUseCase $actualizarConsulta;
    private EliminarConsultaUseCase $eliminarConsulta;

    public function __construct(
        ConsultaRepositoryInterface $consultas,
        ClienteRepositoryInterface $clientes,
        PaqueteRepositoryInterface $paquetes,
    ) {
        $this->crearConsulta = new CrearConsultaUseCase($consultas, $clientes, $paquetes);
        $this->obtenerConsultaPorId = new ObtenerConsultaPorIdUseCase($consultas);
        $this->listarConsultas = new ListarConsultasUseCase($consultas);
        $this->actualizarConsulta = new ActualizarConsultaUseCase($consultas, $clientes, $paquetes);
        $this->eliminarConsulta = new EliminarConsultaUseCase($consultas);
    }

    /** @param array{paquete_id:int,mensaje:string,cliente_id?:int,nombre?:string,apellido?:string,email?:string,telefono?:string,dni?:string,ubicacion?:string} $datos */
    public function crear(array $datos): array
    {
        $datosCliente = null;
        if (!isset($datos['cliente_id'])) {
            $datosCliente = [
                'nombre' => $datos['nombre'] ?? '',
                'apellido' => $datos['apellido'] ?? '',
                'email' => $datos['email'] ?? '',
                'telefono' => $datos['telefono'] ?? '',
                'dni' => $datos['dni'] ?? '',
                'ubicacion' => $datos['ubicacion'] ?? '',
            ];
        }

        $consulta = $this->crearConsulta->execute(new CrearConsultaInput(
            paqueteId: (int) $datos['paquete_id'],
            mensaje: (string) $datos['mensaje'],
            clienteId: isset($datos['cliente_id']) ? (int) $datos['cliente_id'] : null,
            datosCliente: $datosCliente,
        ));

        return $this->serializarConsulta($consulta);
    }

    public function obtenerPorId(int $id): ?array
    {
        $consulta = $this->obtenerConsultaPorId->execute($id);
        if ($consulta === null) {
            return null;
        }
        return $this->serializarConsulta($consulta);
    }

    /** @param array{id:int,cliente_id?:int,paquete_id?:int,mensaje?:string,estado?:string} $datos */
    public function actualizar(array $datos): array
    {
        $consulta = $this->actualizarConsulta->execute(new ActualizarConsultaInput(
            id: (int) $datos['id'],
            clienteId: isset($datos['cliente_id']) ? (int) $datos['cliente_id'] : null,
            paqueteId: isset($datos['paquete_id']) ? (int) $datos['paquete_id'] : null,
            mensaje: $datos['mensaje'] ?? null,
            estado: $datos['estado'] ?? null,
        ));

        return $this->serializarConsulta($consulta);
    }

    public function eliminar(int $id): void
    {
        $this->eliminarConsulta->execute($id);
    }

    /** @param array<string, mixed> $filtros */
    public function listar(array $filtros = []): array
    {
        return array_map(
            fn (Consulta $consulta): array => $this->serializarConsulta($consulta),
            $this->listarConsultas->execute($filtros),
        );
    }

    /** @return array<string, mixed> */
    private function serializarConsulta(Consulta $consulta): array
    {
        return $consulta->toArray();
    }
}
