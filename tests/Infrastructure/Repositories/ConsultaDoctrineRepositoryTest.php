<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Infrastructure\Repositories;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Domain\Models\Consulta;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\ConsultaDoctrineRepository;
use TiendaTurismo\GestionDatos\Tests\Shared\EntityManagerMockFactory;

final class ConsultaDoctrineRepositoryTest extends TestCase
{
    use EntityManagerMockFactory;

    private ConsultaDoctrineRepository $repo;

    protected function setUp(): void
    {
        $this->crearMocksEntityManager($this, Consulta::class);
        $this->repo = new ConsultaDoctrineRepository($this->entityManager);
    }

    public function test_save_persists_and_flushes(): void
    {
        $query = $this->createMock(Query::class);
        $query->method('getOneOrNullResult')->willReturn(null);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('leftJoin')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $this->entityManager
            ->method('createQueryBuilder')
            ->willReturn($qb);

        $usuario = new \TiendaTurismo\GestionDatos\Domain\Models\Usuario('Admin', 'Test', 'a@a.com', 'hash', 'admin', id: 1);
        $destino = new \TiendaTurismo\GestionDatos\Domain\Models\Destino('Buenos Aires', 'CABA', 'Argentina', id: 1);
        $hotel = new \TiendaTurismo\GestionDatos\Domain\Models\Hotel(
            nombre: 'Hotel Test',
            ubicacion: 'Centro',
            descripcion: 'Hotel de prueba',
            destino: $destino,
            id: 1,
        );
        $paquete = new \TiendaTurismo\GestionDatos\Domain\Models\Paquete(
            nombre: 'Paquete Test', descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-01-01'), fechaVuelta: null,
            precio: '100', disponible: true, creadoPor: $usuario,
        );
        $cliente = new \TiendaTurismo\GestionDatos\Domain\Models\Cliente(
            'Juan', 'Pérez', 'juan@test.com', '123456789', '12345678', 'Bs As', id: 1,
        );

        $consulta = new Consulta(
            cliente: $cliente, paquete: $paquete, mensaje: 'Test consulta',
            calificacion: Consulta::CALIFICACION_FRIO,
        );

        $this->entityManager->expects($this->once())->method('persist')->with($consulta);
        $this->entityManager->expects($this->once())->method('flush');

        $this->repo->save($consulta);
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
        $qb->method('addSelect')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('orderBy')->willReturnSelf();
        $qb->method('addOrderBy')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $this->entityManager
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($qb);

        $resultado = $this->repo->findById(5);

        $this->assertNull($resultado);
    }

    public function test_findAll_retorna_lista(): void
    {
        $result = [];

        $query = $this->createMock(Query::class);
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn($result);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('leftJoin')->willReturnSelf();
        $qb->method('addSelect')->willReturnSelf();
        $qb->expects($this->once())
            ->method('orderBy')
            ->with('calificacionOrden', 'ASC')
            ->willReturnSelf();
        $qb->expects($this->once())
            ->method('addOrderBy')
            ->with('c.fechaCreacion', 'DESC')
            ->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $this->entityManager
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($qb);

        $resultado = $this->repo->findAll();

        $this->assertSame($result, $resultado);
    }

    public function test_findAll_filtra_por_calificacion(): void
    {
        $result = [];

        $query = $this->createMock(Query::class);
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn($result);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('leftJoin')->willReturnSelf();
        $qb->method('addSelect')->willReturnSelf();
        $qb->expects($this->once())
            ->method('andWhere')
            ->with('LOWER(c.calificacion) = :calificacion')
            ->willReturnSelf();
        $qb->expects($this->once())
            ->method('setParameter')
            ->with('calificacion', 'frio')
            ->willReturnSelf();
        $qb->expects($this->once())
            ->method('orderBy')
            ->with('calificacionOrden', 'ASC')
            ->willReturnSelf();
        $qb->expects($this->once())
            ->method('addOrderBy')
            ->with('c.fechaCreacion', 'DESC')
            ->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $this->entityManager
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($qb);

        $resultado = $this->repo->findAll(['calificacion' => 'Frio']);

        $this->assertSame($result, $resultado);
    }
}
