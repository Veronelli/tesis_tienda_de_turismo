<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Shared\Mocks;

use PHPUnit\Framework\MockObject\MockObject;
use TiendaTurismo\GestionDatos\Application\Services\ClienteService;

trait ClienteServiceMockTrait
{
    protected function createClienteServiceMock(): ClienteService&MockObject
    {
        return $this->createMock(ClienteService::class);
    }
}
