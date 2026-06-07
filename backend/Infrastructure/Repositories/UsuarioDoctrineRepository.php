<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\Repositories;

use TiendaTurismo\GestionDatos\Domain\Models\Usuario;
use TiendaTurismo\GestionDatos\Domain\Repositories\UsuarioRepositoryInterface;

final class UsuarioDoctrineRepository extends BaseRepository implements UsuarioRepositoryInterface
{
    public function save(Usuario $usuario): void
    {
        $this->entityManager->persist($usuario);
        $this->flush();
    }

    public function update(Usuario $usuario): void
    {
        $existente = $this->findById($usuario->id());

        if ($existente === null) {
            throw new \RuntimeException("Usuario con ID {$usuario->id()} no encontrado.");
        }

        $reflection = new \ReflectionClass($existente);

        foreach (['nombre', 'apellido', 'numeroDocumento', 'email', 'contrasena', 'rol'] as $propiedad) {
            $prop = $reflection->getProperty($propiedad);
            $prop->setValue($existente, $prop->getValue($usuario));
        }

        $fechaProp = $reflection->getProperty('fechaActualizacion');
        $fechaProp->setValue($existente, new \DateTimeImmutable());

        $this->flush();
    }

    public function findById(int $id): ?Usuario
    {
        return $this->entityManager->find(Usuario::class, $id);
    }

    public function findByNumeroDocumento(string $numeroDocumento): ?Usuario
    {
        return $this->entityManager->getRepository(Usuario::class)->findOneBy([
            'numeroDocumento' => $numeroDocumento,
        ]);
    }

    /** @return list<Usuario> */
    public function findAll(): array
    {
        return $this->entityManager->getRepository(Usuario::class)->findAll();
    }
}
