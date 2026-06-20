<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\Repositories;

use TiendaTurismo\GestionDatos\Domain\Models\Cliente;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;

final class ClienteDoctrineRepository extends BaseRepository implements ClienteRepositoryInterface
{
    public function save(Cliente $cliente): void
    {
        $this->entityManager->persist($cliente);
        $this->flush();
    }

    public function findById(int $id): ?Cliente
    {
        return $this->entityManager->find(Cliente::class, $id);
    }

    public function update(Cliente $cliente): void
    {
        $this->flush();
    }

    /** @return list<Cliente> */
    public function findAll(): array
    {
        return $this->entityManager->getRepository(Cliente::class)->findAll();
    }
}
