<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Shared\Fixtures;

use TiendaTurismo\GestionDatos\Domain\Models\Destino;
use TiendaTurismo\GestionDatos\Domain\Models\Hotel;
use TiendaTurismo\GestionDatos\Domain\Models\Paquete;
use TiendaTurismo\GestionDatos\Domain\Models\Usuario;

final class PaqueteFixtures
{
    public static function usuarioAdmin(): Usuario
    {
        return new Usuario(
            nombre: 'Admin',
            apellido: 'Test',
            email: 'admin@test.com',
            contrasena: 'hash',
            rol: 'admin',
            id: 1,
            fechaCreacion: new \DateTimeImmutable('2024-01-01 10:00:00'),
            fechaActualizacion: new \DateTimeImmutable('2024-06-15 14:30:00'),
        );
    }

    public static function usuarioEditor(): Usuario
    {
        return new Usuario(
            nombre: 'Editor',
            apellido: 'Test',
            email: 'editor@test.com',
            contrasena: 'hash',
            rol: 'editor',
            id: 2,
            fechaCreacion: new \DateTimeImmutable('2024-01-01 10:00:00'),
            fechaActualizacion: new \DateTimeImmutable('2024-06-15 14:30:00'),
        );
    }

    public static function destinoBsAs(): Destino
    {
        return new Destino(
            ciudad: 'Buenos Aires',
            estadoProvincia: 'CABA',
            pais: 'Argentina',
            id: 1,
            fechaCreacion: new \DateTimeImmutable('2024-01-01 10:00:00'),
            fechaActualizacion: new \DateTimeImmutable('2024-06-15 14:30:00'),
        );
    }

    public static function destinoCordoba(): Destino
    {
        return new Destino(
            ciudad: 'Córdoba',
            estadoProvincia: 'Córdoba',
            pais: 'Argentina',
            id: 2,
            fechaCreacion: new \DateTimeImmutable('2024-01-01 10:00:00'),
            fechaActualizacion: new \DateTimeImmutable('2024-06-15 14:30:00'),
        );
    }

    public static function hotelUno(): Hotel
    {
        return new Hotel(
            nombre: 'Hotel Sheraton',
            ubicacion: 'Av. Corrientes 1234',
            destino: self::destinoBsAs(),
            id: 1,
            fechaCreacion: new \DateTimeImmutable('2024-01-01 10:00:00'),
            fechaActualizacion: new \DateTimeImmutable('2024-06-15 14:30:00'),
        );
    }

    public static function hotelDos(): Hotel
    {
        return new Hotel(
            nombre: 'Hotel Holiday',
            ubicacion: 'Av. Colón 567',
            destino: self::destinoCordoba(),
            id: 2,
            fechaCreacion: new \DateTimeImmutable('2024-01-01 10:00:00'),
            fechaActualizacion: new \DateTimeImmutable('2024-06-15 14:30:00'),
        );
    }

    public static function paqueteValido(): Paquete
    {
        $paquete = new Paquete(
            nombre: 'Paquete Test',
            descripcion: 'Descripción del paquete',
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: new \DateTimeImmutable('2026-07-22'),
            precio: '1500.00',
            disponible: true,
            creadoPor: self::usuarioAdmin(),
            id: 1,
            fechaCreacion: new \DateTimeImmutable('2024-01-01 10:00:00'),
            fechaActualizacion: new \DateTimeImmutable('2024-06-15 14:30:00'),
        );
        $paquete->syncHoteles([self::hotelUno()]);
        return $paquete;
    }

    public static function paqueteConImagenSecundaria(): Paquete
    {
        $paquete = new Paquete(
            nombre: 'Paquete con Imagen Secundaria',
            descripcion: 'Descripción',
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: new \DateTimeImmutable('2026-07-22'),
            precio: '1500.00',
            disponible: true,
            creadoPor: self::usuarioAdmin(),
            imagenSecundaria: '/uploads/paquetes/secundaria.jpg',
            id: 2,
            fechaCreacion: new \DateTimeImmutable('2024-01-01 10:00:00'),
            fechaActualizacion: new \DateTimeImmutable('2024-06-15 14:30:00'),
        );
        $paquete->syncHoteles([self::hotelUno()]);
        return $paquete;
    }
}
