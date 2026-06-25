<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\Input;

final class CrearConsultaInput
{
    /** @param array<string, string>|null $datosCliente */
    public function __construct(
        public readonly int $paqueteId,
        public readonly string $mensaje,
        public readonly ?int $clienteId = null,
        public readonly ?array $datosCliente = null,
        public readonly ?\DateTimeImmutable $fechaConsulta = null,
    ) {
    }
}
