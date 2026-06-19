<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Repositories;

use TiendaTurismo\GestionDatos\Domain\Models\Destino;

interface DestinoRepositoryInterface
{
    public function save(Destino $destino): void;

    public function findById(int $id): ?Destino;

    public function update(Destino $destino): void;

    /** @return list<Destino> */
    public function findAll(): array;
}
