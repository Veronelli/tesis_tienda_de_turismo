<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Repositories;

use TiendaTurismo\GestionDatos\Domain\Models\Paquete;

interface PaqueteRepositoryInterface
{
    public function save(Paquete $paquete): void;

    public function findById(int $id): ?Paquete;

    public function update(Paquete $paquete): void;

    public function delete(Paquete $paquete): void;

    /** @param array{nombre?:string, mes_partida?:string, destino_id?:int, orden_precio?:string} $filtros */
    public function findAll(array $filtros = []): array;
}
