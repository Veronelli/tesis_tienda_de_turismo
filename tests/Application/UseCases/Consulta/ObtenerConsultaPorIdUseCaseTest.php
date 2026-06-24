<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Consulta;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Consulta\ObtenerConsultaPorIdUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\ConsultaRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\ConsultaFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\ConsultaRepositoryMockTrait;

final class ObtenerConsultaPorIdUseCaseTest extends TestCase
{
    use ConsultaRepositoryMockTrait;

    private ConsultaRepositoryInterface $consultaRepo;
    private ObtenerConsultaPorIdUseCase $useCase;

    protected function setUp(): void
    {
        $this->consultaRepo = $this->createConsultaRepositoryMock();
        $this->useCase = new ObtenerConsultaPorIdUseCase($this->consultaRepo);
    }

    public function test_execute_retorna_consulta_por_id(): void
    {
        $consulta = ConsultaFixtures::consultaPendiente();

        $this->consultaRepo
            ->method('findById')
            ->with(1)
            ->willReturn($consulta);

        $resultado = $this->useCase->execute(1);

        $this->assertSame($consulta, $resultado);
    }

    public function test_execute_retorna_null_si_no_existe(): void
    {
        $this->consultaRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $resultado = $this->useCase->execute(999);

        $this->assertNull($resultado);
    }
}
