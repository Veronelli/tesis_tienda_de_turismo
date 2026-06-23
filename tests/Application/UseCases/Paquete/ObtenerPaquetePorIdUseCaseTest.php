<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Paquete;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Paquete\ObtenerPaquetePorIdUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\PaqueteFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\PaqueteRepositoryMockTrait;

final class ObtenerPaquetePorIdUseCaseTest extends TestCase
{
    use PaqueteRepositoryMockTrait;

    private PaqueteRepositoryInterface $paqueteRepo;
    private ObtenerPaquetePorIdUseCase $useCase;

    protected function setUp(): void
    {
        $this->paqueteRepo = $this->createPaqueteRepositoryMock();
        $this->useCase = new ObtenerPaquetePorIdUseCase($this->paqueteRepo);
    }

    public function test_execute_retorna_paquete_por_id(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();

        $this->paqueteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($paquete);

        $resultado = $this->useCase->execute(1);

        $this->assertSame($paquete, $resultado);
    }

    public function test_execute_retorna_null_si_no_existe(): void
    {
        $this->paqueteRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $resultado = $this->useCase->execute(999);

        $this->assertNull($resultado);
    }
}
