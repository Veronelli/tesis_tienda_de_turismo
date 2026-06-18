<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Hotel;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Hotel\ListarHotelesUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\HotelRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\HotelFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\HotelRepositoryMockTrait;

final class ListarHotelesUseCaseTest extends TestCase
{
    use HotelRepositoryMockTrait;

    private HotelRepositoryInterface $hotelRepo;
    private ListarHotelesUseCase $useCase;

    protected function setUp(): void
    {
        $this->hotelRepo = $this->createHotelRepositoryMock();
        $this->useCase = new ListarHotelesUseCase($this->hotelRepo);
    }

    public function test_execute_retorna_lista_de_hoteles(): void
    {
        $hoteles = [HotelFixtures::hotelValido()];

        $this->hotelRepo
            ->method('findAll')
            ->willReturn($hoteles);

        $resultado = $this->useCase->execute();

        $this->assertCount(1, $resultado);
        $this->assertSame($hoteles, $resultado);
    }

    public function test_execute_retorna_lista_vacia(): void
    {
        $this->hotelRepo
            ->method('findAll')
            ->willReturn([]);

        $resultado = $this->useCase->execute();

        $this->assertCount(0, $resultado);
        $this->assertSame([], $resultado);
    }
}
