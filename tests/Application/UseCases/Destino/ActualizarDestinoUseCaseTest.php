<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Destino;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\Input\ActualizarDestinoInput;
use TiendaTurismo\GestionDatos\Application\UseCases\Destino\ActualizarDestinoUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\DestinoRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\DestinoFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\DestinoRepositoryMockTrait;

final class ActualizarDestinoUseCaseTest extends TestCase
{
    use DestinoRepositoryMockTrait;

    private DestinoRepositoryInterface $destinoRepo;
    private ActualizarDestinoUseCase $useCase;

    protected function setUp(): void
    {
        $this->destinoRepo = $this->createDestinoRepositoryMock();
        $this->useCase = new ActualizarDestinoUseCase($this->destinoRepo);
    }

    public function test_execute_actualiza_destino_existente(): void
    {
        $destino = DestinoFixtures::destinoValido();

        $this->destinoRepo
            ->method('findById')
            ->with(1)
            ->willReturn($destino);

        $this->destinoRepo->expects($this->once())->method('update')->with($destino);

        $input = new ActualizarDestinoInput(1, 'Córdoba', 'Córdoba', 'Argentina');
        $resultado = $this->useCase->execute($input);

        $this->assertSame('Córdoba', $resultado->ciudad());
        $this->assertSame('Córdoba', $resultado->estadoProvincia());
    }

    public function test_execute_lanza_excepcion_si_destino_no_existe(): void
    {
        $this->destinoRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Destino no encontrado.');

        $input = new ActualizarDestinoInput(999, 'Córdoba', 'Córdoba', 'Argentina');
        $this->useCase->execute($input);
    }
}
