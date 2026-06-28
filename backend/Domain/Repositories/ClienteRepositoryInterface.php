<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Repositories;

use TiendaTurismo\GestionDatos\Domain\Models\Cliente;

interface ClienteRepositoryInterface
{
    public function save(Cliente $cliente): void;

    public function findById(int $id): ?Cliente;

    public function findByEmail(string $email): ?Cliente;

    public function findByDni(string $dni): ?Cliente;

    public function update(Cliente $cliente): void;

    /** @return list<Cliente> */
    public function findAll(): array;
}
