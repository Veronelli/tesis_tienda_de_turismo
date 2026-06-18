<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Shared\Fixtures;

use TiendaTurismo\GestionDatos\Domain\Models\Destino;

final class DestinoFixtures
{
    public static function destinoValido(): Destino
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

    public static function destinoSinId(): Destino
    {
        return new Destino(
            ciudad: 'Córdoba',
            estadoProvincia: 'Córdoba',
            pais: 'Argentina',
        );
    }
}
