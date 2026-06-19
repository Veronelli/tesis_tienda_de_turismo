<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Shared\Mocks;

use PHPUnit\Framework\MockObject\MockObject;
use TiendaTurismo\GestionDatos\Application\Services\PaqueteService;

trait PaqueteServiceMockTrait
{
    protected function createPaqueteServiceMock(): PaqueteService&MockObject
    {
        return $this->createMock(PaqueteService::class);
    }
}
