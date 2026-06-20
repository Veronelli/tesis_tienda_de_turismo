<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Paquete;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Paquete\EliminarPaqueteUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\UsuarioRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\PaqueteFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\PaqueteRepositoryMockTrait;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\UsuarioRepositoryMockTrait;

final class EliminarPaqueteUseCaseTest extends TestCase
{
    use PaqueteRepositoryMockTrait;
    use UsuarioRepositoryMockTrait;

    private PaqueteRepositoryInterface $paqueteRepo;
    private UsuarioRepositoryInterface $usuarioRepo;
    private EliminarPaqueteUseCase $useCase;

    protected function setUp(): void
    {
        $this->paqueteRepo = $this->createPaqueteRepositoryMock();
        $this->usuarioRepo = $this->createUsuarioRepositoryMock();
        $this->useCase = new EliminarPaqueteUseCase($this->paqueteRepo, $this->usuarioRepo);
    }

    public function test_execute_elimina_paquete_existente(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();
        $usuario = PaqueteFixtures::usuarioAdmin();

        $this->usuarioRepo
            ->method('findById')
            ->with(1)
            ->willReturn($usuario);

        $this->paqueteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($paquete);

        $this->paqueteRepo->expects($this->once())->method('delete')->with($paquete);

        $this->useCase->execute(1, 1);
    }

    public function test_execute_lanza_excepcion_si_paquete_no_existe(): void
    {
        $usuario = PaqueteFixtures::usuarioAdmin();

        $this->usuarioRepo
            ->method('findById')
            ->with(1)
            ->willReturn($usuario);

        $this->paqueteRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->paqueteRepo->expects($this->never())->method('delete');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Paquete no encontrado.');

        $this->useCase->execute(999, 1);
    }

    public function test_execute_lanza_excepcion_si_usuario_no_existe(): void
    {
        $this->usuarioRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->paqueteRepo->expects($this->never())->method('findById');
        $this->paqueteRepo->expects($this->never())->method('delete');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Usuario responsable no encontrado.');

        $this->useCase->execute(1, 999);
    }
}
