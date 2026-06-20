<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Shared\Mocks;

use PHPUnit\Framework\MockObject\MockObject;
use TiendaTurismo\GestionDatos\Application\Services\HotelService;

trait HotelServiceMockTrait
{
    protected function createHotelServiceMock(): HotelService&MockObject
    {
        return $this->createMock(HotelService::class);
    }
}
