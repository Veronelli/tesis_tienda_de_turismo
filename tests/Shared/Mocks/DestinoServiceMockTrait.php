<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Shared\Mocks;

use PHPUnit\Framework\MockObject\MockObject;
use TiendaTurismo\GestionDatos\Application\Services\DestinoService;

trait DestinoServiceMockTrait
{
    protected function createDestinoServiceMock(): DestinoService&MockObject
    {
        return $this->createMock(DestinoService::class);
    }
}
