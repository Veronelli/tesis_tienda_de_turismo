<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\Input;

final class ActualizarDestinoInput
{
    public function __construct(
        public readonly int $id,
        public readonly string $ciudad,
        public readonly string $estadoProvincia,
        public readonly string $pais,
    ) {
    }
}
