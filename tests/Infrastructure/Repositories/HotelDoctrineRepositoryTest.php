<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Infrastructure\Repositories;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Domain\Models\Hotel;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\HotelDoctrineRepository;
use TiendaTurismo\GestionDatos\Tests\Shared\EntityManagerMockFactory;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\HotelFixtures;

final class HotelDoctrineRepositoryTest extends TestCase
{
    use EntityManagerMockFactory;

    private HotelDoctrineRepository $repo;
    private Hotel $hotel;

    protected function setUp(): void
    {
        $this->crearMocksEntityManager($this, Hotel::class);

        $this->repo = new HotelDoctrineRepository($this->entityManager);

        $this->hotel = HotelFixtures::hotelSinId();
    }

    public function test_save_persists_and_flushes(): void
    {
        $this->entityManager->expects($this->once())->method('persist')->with($this->hotel);
        $this->entityManager->expects($this->once())->method('flush');

        $this->repo->save($this->hotel);
    }

    public function test_update_flushes(): void
    {
        $this->entityManager->expects($this->once())->method('flush');

        $this->repo->update($this->hotel);
    }

    public function test_findById_delega_en_entityManager(): void
    {
        $hotelConId = HotelFixtures::hotelValido();

        $this->entityManager
            ->expects($this->once())
            ->method('find')
            ->with(Hotel::class, 5)
            ->willReturn($hotelConId);

        $resultado = $this->repo->findById(5);

        $this->assertSame($hotelConId, $resultado);
    }

    public function test_findById_retorna_null_si_no_existe(): void
    {
        $this->entityManager
            ->method('find')
            ->with(Hotel::class, 999)
            ->willReturn(null);

        $this->assertNull($this->repo->findById(999));
    }

    public function test_findAll_retorna_lista_de_hoteles(): void
    {
        $hoteles = [$this->hotel];

        $this->entityRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($hoteles);

        $resultado = $this->repo->findAll();

        $this->assertSame($hoteles, $resultado);
    }
}
