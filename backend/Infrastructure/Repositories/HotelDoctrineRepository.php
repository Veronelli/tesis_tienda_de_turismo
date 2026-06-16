<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\Repositories;

use TiendaTurismo\GestionDatos\Domain\Models\Hotel;
use TiendaTurismo\GestionDatos\Domain\Repositories\HotelRepositoryInterface;

final class HotelDoctrineRepository extends BaseRepository implements HotelRepositoryInterface
{
    public function save(Hotel $hotel): void
    {
        $this->entityManager->persist($hotel);
        $this->flush();
    }

    public function findById(int $id): ?Hotel
    {
        return $this->entityManager->find(Hotel::class, $id);
    }

    public function update(Hotel $hotel): void
    {
        $this->flush();
    }

    /** @return list<Hotel> */
    public function findAll(): array
    {
        return $this->entityManager->getRepository(Hotel::class)->findAll();
    }
}
