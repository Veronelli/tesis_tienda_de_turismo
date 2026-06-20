<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Shared\Mocks;

use PHPUnit\Framework\MockObject\MockObject;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;

trait ClienteRepositoryMockTrait
{
    protected function createClienteRepositoryMock(): ClienteRepositoryInterface&MockObject
    {
        return $this->createMock(ClienteRepositoryInterface::class);
    }
}
