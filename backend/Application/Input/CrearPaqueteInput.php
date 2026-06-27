<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\Input;

final class CrearPaqueteInput
{
    /** @param list<int> $hotelesIds */
    public function __construct(
        public readonly string $nombre,
        public readonly ?string $descripcion,
        public readonly \DateTimeImmutable $fechaPartida,
        public readonly ?\DateTimeImmutable $fechaVuelta,
        public readonly string $precio,
        public readonly bool $disponible,
        public readonly int $usuarioResponsableId,
        public readonly array $hotelesIds,
        public readonly bool $desayuno = false,
        public readonly bool $allInclusive = false,
        public readonly bool $pileta = false,
        public readonly ?string $imagenPrincipal = null,
        public readonly ?string $imagenSecundaria = null,
    ) {
    }
}
