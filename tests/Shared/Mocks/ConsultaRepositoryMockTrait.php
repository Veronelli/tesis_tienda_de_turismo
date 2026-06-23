<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Shared\Mocks;

use PHPUnit\Framework\MockObject\MockObject;
use TiendaTurismo\GestionDatos\Domain\Repositories\ConsultaRepositoryInterface;

trait ConsultaRepositoryMockTrait
{
    protected function createConsultaRepositoryMock(): ConsultaRepositoryInterface&MockObject
    {
        return $this->createMock(ConsultaRepositoryInterface::class);
    }
}
