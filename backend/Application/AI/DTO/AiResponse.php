<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\AI\DTO;

final class AiResponse
{
    /** @param array<string, mixed> $raw */
    public function __construct(
        public readonly string $text,
        public readonly string $provider,
        public readonly string $model,
        public readonly array $raw = [],
    ) {
    }
}
