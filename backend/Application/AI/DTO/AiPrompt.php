<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\AI\DTO;

final class AiPrompt
{
    public function __construct(
        public readonly string $instructions,
        public readonly string $input,
        public readonly ?float $temperature = null,
        public readonly string $context = '',
    ) {
    }
}
