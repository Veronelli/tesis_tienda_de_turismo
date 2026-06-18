<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Shared\Fixtures;

use TiendaTurismo\GestionDatos\Domain\Models\Cliente;

final class ClienteFixtures
{
    public static function clienteValido(): Cliente
    {
        return new Cliente(
            nombre: 'Juan',
            apellido: 'Pérez',
            email: 'juan@example.com',
            telefono: '123456789',
            dni: '12345678',
            ubicacion: 'Buenos Aires',
            id: 1,
            fechaCreacion: new \DateTimeImmutable('2024-01-01 10:00:00'),
            fechaActualizacion: new \DateTimeImmutable('2024-06-15 14:30:00'),
        );
    }

    public static function clienteSinId(): Cliente
    {
        return new Cliente(
            nombre: 'María',
            apellido: 'García',
            email: 'maria@example.com',
            telefono: '987654321',
            dni: '87654321',
            ubicacion: 'Córdoba',
        );
    }
}
