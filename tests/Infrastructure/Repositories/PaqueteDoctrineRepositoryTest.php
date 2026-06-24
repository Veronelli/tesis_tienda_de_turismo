<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Infrastructure\Repositories;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Domain\Models\Paquete;
use TiendaTurismo\GestionDatos\Domain\Models\Usuario;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\PaqueteDoctrineRepository;
use TiendaTurismo\GestionDatos\Tests\Shared\EntityManagerMockFactory;

final class PaqueteDoctrineRepositoryTest extends TestCase
{
    use EntityManagerMockFactory;

    private PaqueteDoctrineRepository $repo;

    protected function setUp(): void
    {
        $this->crearMocksEntityManager($this, Paquete::class);
        $this->repo = new PaqueteDoctrineRepository($this->entityManager);
    }

    public function test_save_persists_and_flushes(): void
    {
        $usuario = new Usuario('Admin', 'Test', 'a@a.com', 'hash', 'admin', id: 1);

        $paquete = new Paquete(
            nombre: 'Test',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-01-01'),
            fechaVuelta: null,
            precio: '100',
            disponible: true,
            creadoPor: $usuario,
        );

        $this->entityManager->expects($this->once())->method('persist')->with($paquete);
        $this->entityManager->expects($this->once())->method('flush');

        $this->repo->save($paquete);
    }

    public function test_delete_removes_and_flushes(): void
    {
        $usuario = new Usuario('Admin', 'Test', 'a@a.com', 'hash', 'admin', id: 1);

        $paquete = new Paquete(
            nombre: 'Test',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-01-01'),
            fechaVuelta: null,
            precio: '100',
            disponible: true,
            creadoPor: $usuario,
        );

        $this->entityManager->expects($this->once())->method('remove')->with($paquete);
        $this->entityManager->expects($this->once())->method('flush');

        $this->repo->delete($paquete);
    }

    public function test_findById_delega_en_entityManager(): void
    {
        $query = $this->createMock(Query::class);
        $query->expects($this->once())
            ->method('getOneOrNullResult')
            ->willReturn(null);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('leftJoin')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $this->entityManager
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($qb);

        $resultado = $this->repo->findById(5);

        $this->assertNull($resultado);
    }

    public function test_findAll_retorna_lista_de_paquetes(): void
    {
        $usuario = new Usuario('Admin', 'Test', 'a@a.com', 'hash', 'admin', id: 1);

        $paquetes = [new Paquete(
            nombre: 'Test',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-01-01'),
            fechaVuelta: null,
            precio: '100',
            disponible: true,
            creadoPor: $usuario,
        )];

        $query = $this->createMock(Query::class);
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn($paquetes);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('leftJoin')->willReturnSelf();
        $qb->method('orderBy')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $this->entityManager
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($qb);

        $resultado = $this->repo->findAll();

        $this->assertSame($paquetes, $resultado);
    }
}
