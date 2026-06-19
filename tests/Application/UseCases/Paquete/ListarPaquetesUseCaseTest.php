<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Paquete;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Paquete\ListarPaquetesUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\PaqueteFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\PaqueteRepositoryMockTrait;

final class ListarPaquetesUseCaseTest extends TestCase
{
    use PaqueteRepositoryMockTrait;

    private PaqueteRepositoryInterface $paqueteRepo;
    private ListarPaquetesUseCase $useCase;

    protected function setUp(): void
    {
        $this->paqueteRepo = $this->createPaqueteRepositoryMock();
        $this->useCase = new ListarPaquetesUseCase($this->paqueteRepo);
    }

    public function test_execute_retorna_lista_de_paquetes(): void
    {
        $paquetes = [PaqueteFixtures::paqueteValido()];

        $this->paqueteRepo
            ->method('findAll')
            ->with([])
            ->willReturn($paquetes);

        $resultado = $this->useCase->execute();

        $this->assertCount(1, $resultado);
        $this->assertSame($paquetes, $resultado);
    }

    public function test_execute_retorna_lista_vacia(): void
    {
        $this->paqueteRepo
            ->method('findAll')
            ->with([])
            ->willReturn([]);

        $resultado = $this->useCase->execute();

        $this->assertCount(0, $resultado);
        $this->assertSame([], $resultado);
    }

    public function test_execute_filtra_por_nombre(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();

        $this->paqueteRepo
            ->expects($this->once())
            ->method('findAll')
            ->with(['nombre' => 'costa'])
            ->willReturn([$paquete]);

        $resultado = $this->useCase->execute(['nombre' => 'costa']);

        $this->assertCount(1, $resultado);
    }

    public function test_execute_filtra_por_mes_partida(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();

        $this->paqueteRepo
            ->expects($this->once())
            ->method('findAll')
            ->with(['mes_partida' => 7])
            ->willReturn([$paquete]);

        $resultado = $this->useCase->execute(['mes_partida' => 7]);

        $this->assertCount(1, $resultado);
    }

    public function test_execute_filtra_por_destino(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();

        $this->paqueteRepo
            ->expects($this->once())
            ->method('findAll')
            ->with(['destino_id' => 1])
            ->willReturn([$paquete]);

        $resultado = $this->useCase->execute(['destino_id' => 1]);

        $this->assertCount(1, $resultado);
    }

    public function test_execute_ordena_por_precio_ascendente(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();

        $this->paqueteRepo
            ->expects($this->once())
            ->method('findAll')
            ->with(['orden_precio' => 'asc'])
            ->willReturn([$paquete]);

        $resultado = $this->useCase->execute(['orden_precio' => 'asc']);

        $this->assertCount(1, $resultado);
    }

    public function test_execute_ordena_por_precio_descendente(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();

        $this->paqueteRepo
            ->expects($this->once())
            ->method('findAll')
            ->with(['orden_precio' => 'desc'])
            ->willReturn([$paquete]);

        $resultado = $this->useCase->execute(['orden_precio' => 'desc']);

        $this->assertCount(1, $resultado);
    }
}
