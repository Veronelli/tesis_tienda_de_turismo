<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Repositories;

use TiendaTurismo\GestionDatos\Domain\Models\Hotel;

interface HotelRepositoryInterface
{
    public function save(Hotel $hotel): void;

    public function findById(int $id): ?Hotel;

    public function update(Hotel $hotel): void;

    /** @return list<Hotel> */
    public function findAll(): array;
}
