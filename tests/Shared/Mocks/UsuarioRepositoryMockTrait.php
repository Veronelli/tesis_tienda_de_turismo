<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Shared\Mocks;

use PHPUnit\Framework\MockObject\MockObject;
use TiendaTurismo\GestionDatos\Domain\Repositories\UsuarioRepositoryInterface;

trait UsuarioRepositoryMockTrait
{
    protected function createUsuarioRepositoryMock(): UsuarioRepositoryInterface&MockObject
    {
        return $this->createMock(UsuarioRepositoryInterface::class);
    }
}
