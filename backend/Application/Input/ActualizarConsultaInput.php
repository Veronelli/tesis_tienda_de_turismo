<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\Input;

final class ActualizarConsultaInput
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $clienteId = null,
        public readonly ?int $paqueteId = null,
        public readonly ?string $mensaje = null,
        public readonly ?string $estado = null,
        public readonly ?\DateTimeImmutable $fechaConsulta = null,
        public readonly int $usuarioResponsableId = 0,
    ) {
    }
}
