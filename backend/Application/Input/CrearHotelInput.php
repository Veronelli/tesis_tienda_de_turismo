<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\Input;

final class CrearHotelInput
{
    public function __construct(
        public readonly string $nombre,
        public readonly string $ubicacion,
        public readonly int $destinoId,
    ) {
    }
}
