<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Destino;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Destino\ObtenerDestinoPorIdUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\DestinoRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\DestinoFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\DestinoRepositoryMockTrait;

final class ObtenerDestinoPorIdUseCaseTest extends TestCase
{
    use DestinoRepositoryMockTrait;

    private DestinoRepositoryInterface $destinoRepo;
    private ObtenerDestinoPorIdUseCase $useCase;

    protected function setUp(): void
    {
        $this->destinoRepo = $this->createDestinoRepositoryMock();
        $this->useCase = new ObtenerDestinoPorIdUseCase($this->destinoRepo);
    }

    public function test_execute_retorna_destino_por_id(): void
    {
        $destino = DestinoFixtures::destinoValido();

        $this->destinoRepo
            ->method('findById')
            ->with(1)
            ->willReturn($destino);

        $resultado = $this->useCase->execute(1);

        $this->assertSame($destino, $resultado);
    }

    public function test_execute_retorna_null_si_no_existe(): void
    {
        $this->destinoRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $resultado = $this->useCase->execute(999);

        $this->assertNull($resultado);
    }
}
