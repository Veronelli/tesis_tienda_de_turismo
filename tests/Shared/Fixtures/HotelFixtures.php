<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Shared\Fixtures;

use TiendaTurismo\GestionDatos\Domain\Models\Destino;
use TiendaTurismo\GestionDatos\Domain\Models\Hotel;

final class HotelFixtures
{
    public static function destinoValido(): Destino
    {
        return new Destino(
            ciudad: 'Buenos Aires',
            estadoProvincia: 'CABA',
            pais: 'Argentina',
            id: 1,
        );
    }

    public static function hotelValido(?Destino $destino = null): Hotel
    {
        $destino ??= self::destinoValido();
        return new Hotel(
            nombre: 'Hotel Sheraton',
            ubicacion: 'Av. Corrientes 1234',
            descripcion: 'Hotel céntrico en Buenos Aires.',
            destino: $destino,
            id: 1,
            fechaCreacion: new \DateTimeImmutable('2024-01-01 10:00:00'),
            fechaActualizacion: new \DateTimeImmutable('2024-06-15 14:30:00'),
        );
    }

    public static function hotelSinId(?Destino $destino = null): Hotel
    {
        $destino ??= self::destinoValido();
        return new Hotel(
            nombre: 'Hotel Nuevo',
            ubicacion: 'Calle Falsa 456',
            descripcion: 'Hotel de referencia.',
            destino: $destino,
        );
    }
}
