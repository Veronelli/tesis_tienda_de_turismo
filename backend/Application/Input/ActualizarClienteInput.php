<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\Input;

final class ActualizarClienteInput
{
    public function __construct(
        public readonly int $id,
        public readonly string $nombre,
        public readonly string $apellido,
        public readonly string $email,
        public readonly string $telefono,
        public readonly string $dni,
        public readonly string $ubicacion,
    ) {
    }
}
