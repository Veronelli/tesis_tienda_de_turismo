<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\Repositories;

use Doctrine\ORM\QueryBuilder;
use TiendaTurismo\GestionDatos\Domain\Models\Consulta;
use TiendaTurismo\GestionDatos\Domain\Repositories\ConsultaRepositoryInterface;

final class ConsultaDoctrineRepository extends BaseRepository implements ConsultaRepositoryInterface
{
    public function save(Consulta $consulta): void
    {
        $this->entityManager->persist($consulta);
        $this->flush();
    }

    public function findById(int $id): ?Consulta
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('c, cli, p')
            ->from(Consulta::class, 'c')
            ->leftJoin('c.cliente', 'cli')
            ->leftJoin('c.paquete', 'p')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function update(Consulta $consulta): void
    {
        $this->flush();
    }

    /** @param array{estado?:string, calificacion?:string, cliente?:string, paquete_id?:int, fecha_desde?:string, fecha_hasta?:string} $filtros */
    public function findAll(array $filtros = []): array
    {
        $qb = $this->entityManager
            ->createQueryBuilder()
            ->select('c, cli, p')
            ->from(Consulta::class, 'c')
            ->leftJoin('c.cliente', 'cli')
            ->leftJoin('c.paquete', 'p');

        $this->applyFilters($qb, $filtros);

        $qb->addSelect("CASE
            WHEN LOWER(COALESCE(c.calificacion, '')) = 'caliente' THEN 1
            WHEN LOWER(COALESCE(c.calificacion, '')) = 'tibio' THEN 2
            WHEN LOWER(COALESCE(c.calificacion, '')) = 'frio' THEN 3
            ELSE 4
        END AS HIDDEN calificacionOrden");
        $qb->orderBy('calificacionOrden', 'ASC')
            ->addOrderBy('c.fechaCreacion', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /** @param array{estado?:string, calificacion?:string, cliente?:string, paquete_id?:int, fecha_desde?:string, fecha_hasta?:string} $filtros */
    private function applyFilters(QueryBuilder $qb, array $filtros): void
    {
        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $qb->andWhere('c.estado = :estado')
                ->setParameter('estado', $filtros['estado']);
        }

        if (isset($filtros['calificacion']) && $filtros['calificacion'] !== '') {
            $qb->andWhere('LOWER(c.calificacion) = :calificacion')
                ->setParameter('calificacion', strtolower($filtros['calificacion']));
        }

        if (isset($filtros['cliente']) && $filtros['cliente'] !== '') {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('cli.nombre', ':cliente'),
                    $qb->expr()->like('cli.apellido', ':cliente'),
                    $qb->expr()->like('cli.email', ':cliente'),
                    $qb->expr()->like('cli.dni', ':cliente'),
                )
            )->setParameter('cliente', '%' . $filtros['cliente'] . '%');
        }

        if (isset($filtros['paquete_id'])) {
            $qb->andWhere('p.id = :paqueteId')
                ->setParameter('paqueteId', (int) $filtros['paquete_id']);
        }

        if (isset($filtros['fecha_desde']) && $filtros['fecha_desde'] !== '') {
            $qb->andWhere('c.fechaCreacion >= :fechaDesde')
                ->setParameter('fechaDesde', new \DateTimeImmutable($filtros['fecha_desde']));
        }

        if (isset($filtros['fecha_hasta']) && $filtros['fecha_hasta'] !== '') {
            $qb->andWhere('c.fechaCreacion <= :fechaHasta')
                ->setParameter('fechaHasta', new \DateTimeImmutable($filtros['fecha_hasta'] . ' 23:59:59'));
        }
    }
}
