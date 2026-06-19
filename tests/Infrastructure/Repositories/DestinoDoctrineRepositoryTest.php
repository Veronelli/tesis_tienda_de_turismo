<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Infrastructure\Repositories;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Domain\Models\Destino;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\DestinoDoctrineRepository;
use TiendaTurismo\GestionDatos\Tests\Shared\EntityManagerMockFactory;

final class DestinoDoctrineRepositoryTest extends TestCase
{
    use EntityManagerMockFactory;

    private DestinoDoctrineRepository $repo;
    private Destino $destino;

    protected function setUp(): void
    {
        $this->crearMocksEntityManager($this, Destino::class);

        $this->repo = new DestinoDoctrineRepository($this->entityManager);

        $this->destino = new Destino(
            ciudad: 'Buenos Aires',
            estadoProvincia: 'CABA',
            pais: 'Argentina',
        );
    }

    public function test_save_persists_and_flushes(): void
    {
        $this->entityManager->expects($this->once())->method('persist')->with($this->destino);
        $this->entityManager->expects($this->once())->method('flush');

        $this->repo->save($this->destino);
    }

    public function test_findById_delega_en_entityManager(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('find')
            ->with(Destino::class, 5)
            ->willReturn($this->destino);

        $resultado = $this->repo->findById(5);

        $this->assertSame($this->destino, $resultado);
    }

    public function test_findById_retorna_null_si_no_existe(): void
    {
        $this->entityManager
            ->method('find')
            ->with(Destino::class, 999)
            ->willReturn(null);

        $this->assertNull($this->repo->findById(999));
    }

    public function test_findAll_retorna_lista_de_destinos(): void
    {
        $destinos = [$this->destino];

        $this->entityRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($destinos);

        $resultado = $this->repo->findAll();

        $this->assertSame($destinos, $resultado);
    }
}
