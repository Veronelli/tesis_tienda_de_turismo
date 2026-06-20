<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Shared\Mocks;

use PHPUnit\Framework\MockObject\MockObject;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;

trait PaqueteRepositoryMockTrait
{
    protected function createPaqueteRepositoryMock(): PaqueteRepositoryInterface&MockObject
    {
        return $this->createMock(PaqueteRepositoryInterface::class);
    }
}
