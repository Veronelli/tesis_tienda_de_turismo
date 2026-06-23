<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Consulta;

use TiendaTurismo\GestionDatos\Domain\Repositories\ConsultaRepositoryInterface;

final class EliminarConsultaUseCase
{
    public function __construct(
        private readonly ConsultaRepositoryInterface $consultas,
    ) {
    }

    public function execute(int $id): void
    {
        $consulta = $this->consultas->findById($id);
        if ($consulta === null) {
            throw new \RuntimeException('Consulta no encontrada.');
        }
        $this->consultas->delete($consulta);
    }
}
