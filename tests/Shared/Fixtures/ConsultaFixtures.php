<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Shared\Fixtures;

use TiendaTurismo\GestionDatos\Domain\Models\Cliente;
use TiendaTurismo\GestionDatos\Domain\Models\Consulta;

final class ConsultaFixtures
{
    public static function clienteValido(): Cliente
    {
        return ClienteFixtures::clienteValido();
    }

    public static function clienteSinId(): Cliente
    {
        return ClienteFixtures::clienteSinId();
    }

    public static function consultaPendiente(): Consulta
    {
        return new Consulta(
            cliente: self::clienteValido(),
            paquete: PaqueteFixtures::paqueteValido(),
            mensaje: 'Quiero información sobre este paquete.',
            calificacion: Consulta::CALIFICACION_FRIO,
            fechaConsulta: new \DateTimeImmutable('2026-06-01 10:00:00'),
            id: 1,
            fechaCreacion: new \DateTimeImmutable('2026-06-01 10:00:00'),
            fechaActualizacion: new \DateTimeImmutable('2026-06-01 10:00:00'),
        );
    }

    public static function consultaRespondida(): Consulta
    {
        $consulta = new Consulta(
            cliente: self::clienteValido(),
            paquete: PaqueteFixtures::paqueteValido(),
            mensaje: 'Consulta respondida.',
            calificacion: Consulta::CALIFICACION_TIBIO,
            fechaConsulta: new \DateTimeImmutable('2026-06-02 10:00:00'),
            id: 2,
            fechaCreacion: new \DateTimeImmutable('2026-06-02 10:00:00'),
            fechaActualizacion: new \DateTimeImmutable('2026-06-03 10:00:00'),
        );
        $consulta->update(estado: Consulta::ESTADO_RESPONDIDA);
        return $consulta;
    }
}
