<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\Repositories;

use TiendaTurismo\GestionDatos\Domain\Models\Destino;
use TiendaTurismo\GestionDatos\Domain\Repositories\DestinoRepositoryInterface;

final class DestinoDoctrineRepository extends BaseRepository implements DestinoRepositoryInterface
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

    /** @return list<Destino> */
    public function findAll(): array
    {
        return $this->entityManager->getRepository(Destino::class)->findAll();
    }
}
