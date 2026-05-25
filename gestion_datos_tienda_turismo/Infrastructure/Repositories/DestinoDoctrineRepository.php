<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\Repositories;

use TiendaTurismo\GestionDatos\Domain\Models\Destino;

final class DestinoDoctrineRepository extends BaseRepository
{
    public function save(Destino $destino): void
    {
        $this->entityManager->persist($destino);
        $this->flush();
    }

    public function findById(int $id): ?Destino
    {
        return $this->entityManager->find(Destino::class, $id);
    }
}
