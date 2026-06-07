<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Repositories;

use TiendaTurismo\GestionDatos\Domain\Models\Usuario;

interface UsuarioRepositoryInterface
{
    public function save(Usuario $usuario): void;

    public function update(Usuario $usuario): void;

    public function findById(int $id): ?Usuario;

    public function findByNumeroDocumento(string $numeroDocumento): ?Usuario;

    public function findByEmail(string $email): ?Usuario;

    /** @return list<Usuario> */
    public function findAll(): array;
}
