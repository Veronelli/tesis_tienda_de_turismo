<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Consulta;

use TiendaTurismo\GestionDatos\Domain\Models\Consulta;
use TiendaTurismo\GestionDatos\Domain\Repositories\ConsultaRepositoryInterface;

final class ObtenerConsultaPorIdUseCase
{
    public function __construct(
        private readonly ConsultaRepositoryInterface $consultas,
    ) {
    }

    public function execute(int $id): ?Consulta
    {
        return $this->consultas->findById($id);
    }
}
