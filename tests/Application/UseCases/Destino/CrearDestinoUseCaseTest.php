<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Destino;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\Input\CrearDestinoInput;
use TiendaTurismo\GestionDatos\Application\UseCases\Destino\CrearDestinoUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\DestinoRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\DestinoRepositoryMockTrait;

final class CrearDestinoUseCaseTest extends TestCase
{
    use DestinoRepositoryMockTrait;

    private DestinoRepositoryInterface $destinoRepo;
    private CrearDestinoUseCase $useCase;

    protected function setUp(): void
    {
        $this->destinoRepo = $this->createDestinoRepositoryMock();
        $this->useCase = new CrearDestinoUseCase($this->destinoRepo);
    }

    public function test_execute_crea_y_guarda_destino(): void
    {
        $this->destinoRepo->expects($this->once())->method('save');

        $input = new CrearDestinoInput('Buenos Aires', 'CABA', 'Argentina');
        $destino = $this->useCase->execute($input);

        $this->assertSame('Buenos Aires', $destino->ciudad());
        $this->assertSame('CABA', $destino->estadoProvincia());
        $this->assertSame('Argentina', $destino->pais());
    }

    public function test_execute_lanza_excepcion_si_ciudad_esta_vacia(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ciudad es obligatorio');

        $input = new CrearDestinoInput('', 'CABA', 'Argentina');
        $this->useCase->execute($input);
    }

    public function test_execute_lanza_excepcion_si_pais_esta_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('pais es obligatorio');

        $input = new CrearDestinoInput('Buenos Aires', 'CABA', '');
        $this->useCase->execute($input);
    }
}
