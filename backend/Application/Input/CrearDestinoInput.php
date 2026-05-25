<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\Input;

final class CrearDestinoInput
{
    public function __construct(
        public readonly string $ciudad,
        public readonly string $estadoProvincia,
        public readonly string $pais,
    ) {
    }
}
