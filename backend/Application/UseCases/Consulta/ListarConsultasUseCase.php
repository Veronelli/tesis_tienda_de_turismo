<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Consulta;

use TiendaTurismo\GestionDatos\Domain\Models\Consulta;
use TiendaTurismo\GestionDatos\Domain\Repositories\ConsultaRepositoryInterface;

final class ListarConsultasUseCase
{
    public function __construct(
        private readonly ConsultaRepositoryInterface $consultas,
    ) {
    }

    /** @param array{estado?:string, cliente?:string, paquete_id?:int, fecha_desde?:string, fecha_hasta?:string} $filtros */
    public function execute(array $filtros = []): array
    {
        return $this->consultas->findAll($filtros);
    }
}
