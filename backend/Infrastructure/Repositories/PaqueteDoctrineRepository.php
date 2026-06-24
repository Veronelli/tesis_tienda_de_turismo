<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\Repositories;

use Doctrine\ORM\QueryBuilder;
use TiendaTurismo\GestionDatos\Domain\Models\Paquete;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;

final class PaqueteDoctrineRepository extends BaseRepository implements PaqueteRepositoryInterface
{
    public function save(Paquete $paquete): void
    {
        $this->entityManager->persist($paquete);
        $this->flush();
    }

    public function findById(int $id): ?Paquete
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('p, h, d')
            ->from(Paquete::class, 'p')
            ->leftJoin('p.hoteles', 'h')
            ->leftJoin('h.destino', 'd')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function update(Paquete $paquete): void
    {
        $this->flush();
    }

    /** @param array{nombre?:string, mes_partida?:int, destino_id?:int, orden_precio?:string} $filtros */
    public function findAll(array $filtros = []): array
    {
        $qb = $this->entityManager
            ->createQueryBuilder()
            ->select('p, h, d')
            ->from(Paquete::class, 'p')
            ->leftJoin('p.hoteles', 'h')
            ->leftJoin('h.destino', 'd');

        $this->applyFilters($qb, $filtros);

        return $qb->getQuery()->getResult();
    }

    /** @param array{nombre?:string, mes_partida?:int, destino_id?:int, orden_precio?:string} $filtros */
    private function applyFilters(QueryBuilder $qb, array $filtros): void
    {
        if (isset($filtros['nombre']) && $filtros['nombre'] !== '') {
            $qb->andWhere('p.nombre LIKE :nombre')
                ->setParameter('nombre', '%' . $filtros['nombre'] . '%');
        }

        if (isset($filtros['mes_partida'])) {
            $qb->andWhere('MONTH(p.fechaPartida) = :mes')
                ->setParameter('mes', (int) $filtros['mes_partida']);
        }

        if (isset($filtros['destino_id'])) {
            $qb->andWhere('d.id = :destinoId')
                ->setParameter('destinoId', (int) $filtros['destino_id']);
        }

        if (isset($filtros['orden_precio'])) {
            $direccion = strtolower($filtros['orden_precio']) === 'asc' ? 'ASC' : 'DESC';
            $qb->orderBy('p.precio', $direccion);
        } else {
            $qb->orderBy('p.fechaPartida', 'ASC');
        }
    }
}
