<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Shared\Mocks;

use PHPUnit\Framework\MockObject\MockObject;
use TiendaTurismo\GestionDatos\Domain\Repositories\HotelRepositoryInterface;

trait HotelRepositoryMockTrait
{
    protected function createHotelRepositoryMock(): HotelRepositoryInterface&MockObject
    {
        return $this->createMock(HotelRepositoryInterface::class);
    }
}
