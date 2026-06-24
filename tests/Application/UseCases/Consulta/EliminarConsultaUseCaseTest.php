<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Consulta;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Consulta\EliminarConsultaUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\ConsultaRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\ConsultaFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\ConsultaRepositoryMockTrait;

final class EliminarConsultaUseCaseTest extends TestCase
{
    use ConsultaRepositoryMockTrait;

    private ConsultaRepositoryInterface $consultaRepo;
    private EliminarConsultaUseCase $useCase;

    protected function setUp(): void
    {
        $this->consultaRepo = $this->createConsultaRepositoryMock();
        $this->useCase = new EliminarConsultaUseCase($this->consultaRepo);
    }

    public function test_execute_elimina_consulta_existente(): void
    {
        $consulta = ConsultaFixtures::consultaPendiente();

        $this->consultaRepo
            ->method('findById')
            ->with(1)
            ->willReturn($consulta);

        $this->consultaRepo->expects($this->once())->method('delete')->with($consulta);

        $this->useCase->execute(1);
    }

    public function test_execute_lanza_excepcion_si_consulta_no_existe(): void
    {
        $this->consultaRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Consulta no encontrada.');

        $this->useCase->execute(999);
    }
}
