<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Hotel;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\Input\ActualizarHotelInput;
use TiendaTurismo\GestionDatos\Application\UseCases\Hotel\ActualizarHotelUseCase;
use TiendaTurismo\GestionDatos\Domain\Models\Destino;
use TiendaTurismo\GestionDatos\Domain\Repositories\DestinoRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\HotelRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\HotelFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\DestinoRepositoryMockTrait;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\HotelRepositoryMockTrait;

final class ActualizarHotelUseCaseTest extends TestCase
{
    use HotelRepositoryMockTrait;
    use DestinoRepositoryMockTrait;

    private HotelRepositoryInterface $hotelRepo;
    private DestinoRepositoryInterface $destinoRepo;
    private ActualizarHotelUseCase $useCase;

    protected function setUp(): void
    {
        $this->hotelRepo = $this->createHotelRepositoryMock();
        $this->destinoRepo = $this->createDestinoRepositoryMock();
        $this->useCase = new ActualizarHotelUseCase($this->hotelRepo, $this->destinoRepo);
    }

    public function test_execute_actualiza_hotel_existente(): void
    {
        $destinoOriginal = HotelFixtures::destinoValido();
        $nuevoDestino = new Destino('Córdoba', 'Córdoba', 'Argentina', id: 2);
        $hotel = HotelFixtures::hotelValido($destinoOriginal);

        $this->hotelRepo
            ->method('findById')
            ->with(1)
            ->willReturn($hotel);

        $this->destinoRepo
            ->method('findById')
            ->with(2)
            ->willReturn($nuevoDestino);

        $this->hotelRepo->expects($this->once())->method('update')->with($hotel);

        $input = new ActualizarHotelInput(1, 'Hotel Modificado', 'Nueva Calle 789', 2);
        $resultado = $this->useCase->execute($input);

        $this->assertSame('Hotel Modificado', $resultado->nombre());
        $this->assertSame('Nueva Calle 789', $resultado->ubicacion());
        $this->assertSame($nuevoDestino, $resultado->destino());
    }

    public function test_execute_lanza_excepcion_si_hotel_no_existe(): void
    {
        $this->hotelRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Hotel no encontrado.');

        $input = new ActualizarHotelInput(999, 'Hotel', 'Calle', 1);
        $this->useCase->execute($input);
    }

    public function test_execute_lanza_excepcion_si_destino_no_existe(): void
    {
        $hotel = HotelFixtures::hotelValido();

        $this->hotelRepo
            ->method('findById')
            ->with(1)
            ->willReturn($hotel);

        $this->destinoRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Destino no encontrado.');

        $input = new ActualizarHotelInput(1, 'Hotel', 'Calle', 999);
        $this->useCase->execute($input);
    }
}
