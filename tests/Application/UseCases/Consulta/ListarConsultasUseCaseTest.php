<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Consulta;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Consulta\ListarConsultasUseCase;
use TiendaTurismo\GestionDatos\Domain\Models\Consulta;
use TiendaTurismo\GestionDatos\Domain\Repositories\ConsultaRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\ConsultaFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\ConsultaRepositoryMockTrait;

final class ListarConsultasUseCaseTest extends TestCase
{
    use ConsultaRepositoryMockTrait;

    private ConsultaRepositoryInterface $consultaRepo;
    private ListarConsultasUseCase $useCase;

    protected function setUp(): void
    {
        $this->consultaRepo = $this->createConsultaRepositoryMock();
        $this->useCase = new ListarConsultasUseCase($this->consultaRepo);
    }

    public function test_execute_retorna_lista_de_consultas(): void
    {
        $consultas = [ConsultaFixtures::consultaPendiente()];

        $this->consultaRepo
            ->method('findAll')
            ->with([])
            ->willReturn($consultas);

        $resultado = $this->useCase->execute();

        $this->assertCount(1, $resultado);
        $this->assertSame($consultas, $resultado);
    }

    public function test_execute_retorna_lista_vacia(): void
    {
        $this->consultaRepo
            ->method('findAll')
            ->with([])
            ->willReturn([]);

        $resultado = $this->useCase->execute();

        $this->assertCount(0, $resultado);
        $this->assertSame([], $resultado);
    }

    public function test_execute_filtra_por_estado(): void
    {
        $consulta = ConsultaFixtures::consultaPendiente();

        $this->consultaRepo
            ->expects($this->once())
            ->method('findAll')
            ->with(['estado' => Consulta::ESTADO_PENDIENTE])
            ->willReturn([$consulta]);

        $resultado = $this->useCase->execute(['estado' => Consulta::ESTADO_PENDIENTE]);

        $this->assertCount(1, $resultado);
    }
}
