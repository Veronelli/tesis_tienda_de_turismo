<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Shared\Mocks;

use PHPUnit\Framework\MockObject\MockObject;
use TiendaTurismo\GestionDatos\Domain\Repositories\DestinoRepositoryInterface;

trait DestinoRepositoryMockTrait
{
    protected function createDestinoRepositoryMock(): DestinoRepositoryInterface&MockObject
    {
        return $this->createMock(DestinoRepositoryInterface::class);
    }
}
