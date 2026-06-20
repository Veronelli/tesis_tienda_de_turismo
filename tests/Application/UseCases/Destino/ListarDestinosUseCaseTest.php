<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Destino;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Destino\ListarDestinosUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\DestinoRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\DestinoFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\DestinoRepositoryMockTrait;

final class ListarDestinosUseCaseTest extends TestCase
{
    use DestinoRepositoryMockTrait;

    private DestinoRepositoryInterface $destinoRepo;
    private ListarDestinosUseCase $useCase;

    protected function setUp(): void
    {
        $this->destinoRepo = $this->createDestinoRepositoryMock();
        $this->useCase = new ListarDestinosUseCase($this->destinoRepo);
    }

    public function test_execute_retorna_lista_de_destinos(): void
    {
        $destinos = [DestinoFixtures::destinoValido()];

        $this->destinoRepo
            ->method('findAll')
            ->willReturn($destinos);

        $resultado = $this->useCase->execute();

        $this->assertCount(1, $resultado);
        $this->assertSame($destinos, $resultado);
    }

    public function test_execute_retorna_lista_vacia(): void
    {
        $this->destinoRepo
            ->method('findAll')
            ->willReturn([]);

        $resultado = $this->useCase->execute();

        $this->assertCount(0, $resultado);
        $this->assertSame([], $resultado);
    }
}
