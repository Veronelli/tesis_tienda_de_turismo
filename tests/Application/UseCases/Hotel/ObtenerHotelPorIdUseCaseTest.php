<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Hotel;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Hotel\ObtenerHotelPorIdUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\HotelRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\HotelFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\HotelRepositoryMockTrait;

final class ObtenerHotelPorIdUseCaseTest extends TestCase
{
    use HotelRepositoryMockTrait;

    private HotelRepositoryInterface $hotelRepo;
    private ObtenerHotelPorIdUseCase $useCase;

    protected function setUp(): void
    {
        $this->hotelRepo = $this->createHotelRepositoryMock();
        $this->useCase = new ObtenerHotelPorIdUseCase($this->hotelRepo);
    }

    public function test_execute_retorna_hotel_por_id(): void
    {
        $hotel = HotelFixtures::hotelValido();

        $this->hotelRepo
            ->method('findById')
            ->with(1)
            ->willReturn($hotel);

        $resultado = $this->useCase->execute(1);

        $this->assertSame($hotel, $resultado);
    }

    public function test_execute_retorna_null_si_no_existe(): void
    {
        $this->hotelRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $resultado = $this->useCase->execute(999);

        $this->assertNull($resultado);
    }
}
