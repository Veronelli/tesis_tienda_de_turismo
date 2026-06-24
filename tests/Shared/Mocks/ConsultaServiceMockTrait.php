<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Shared\Mocks;

use PHPUnit\Framework\MockObject\MockObject;
use TiendaTurismo\GestionDatos\Application\Services\ConsultaService;

trait ConsultaServiceMockTrait
{
    protected function createConsultaServiceMock(): ConsultaService&MockObject
    {
        return $this->createMock(ConsultaService::class);
    }
}
