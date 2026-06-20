<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Hotel;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\Input\CrearHotelInput;
use TiendaTurismo\GestionDatos\Application\UseCases\Hotel\CrearHotelUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\DestinoRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\HotelRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\HotelFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\DestinoRepositoryMockTrait;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\HotelRepositoryMockTrait;

final class CrearHotelUseCaseTest extends TestCase
{
    use HotelRepositoryMockTrait;
    use DestinoRepositoryMockTrait;

    private HotelRepositoryInterface $hotelRepo;
    private DestinoRepositoryInterface $destinoRepo;
    private CrearHotelUseCase $useCase;

    protected function setUp(): void
    {
        $this->hotelRepo = $this->createHotelRepositoryMock();
        $this->destinoRepo = $this->createDestinoRepositoryMock();
        $this->useCase = new CrearHotelUseCase($this->hotelRepo, $this->destinoRepo);
    }

    public function test_execute_crea_y_guarda_hotel(): void
    {
        $destino = HotelFixtures::destinoValido();

        $this->destinoRepo
            ->method('findById')
            ->with(1)
            ->willReturn($destino);

        $this->hotelRepo->expects($this->once())->method('save');

        $input = new CrearHotelInput('Hotel Test', 'Calle 123', 1);
        $hotel = $this->useCase->execute($input);

        $this->assertSame('Hotel Test', $hotel->nombre());
        $this->assertSame('Calle 123', $hotel->ubicacion());
        $this->assertSame($destino, $hotel->destino());
    }

    public function test_execute_lanza_excepcion_si_destino_no_existe(): void
    {
        $this->destinoRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Destino no encontrado.');

        $input = new CrearHotelInput('Hotel Test', 'Calle 123', 999);
        $this->useCase->execute($input);
    }
}
