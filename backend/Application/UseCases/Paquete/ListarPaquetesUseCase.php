<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Paquete;

use TiendaTurismo\GestionDatos\Domain\Models\Paquete;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;

final class ListarPaquetesUseCase
{
    public function __construct(
        private readonly PaqueteRepositoryInterface $paquetes,
    ) {
    }

    /** @param array{nombre?:string, mes_partida?:int, destino_id?:int, orden_precio?:string} $filtros */
    public function execute(array $filtros = []): array
    {
        return $this->paquetes->findAll($filtros);
    }
}
