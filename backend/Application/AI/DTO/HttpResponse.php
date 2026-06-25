<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\AI\DTO;

final class HttpResponse
{
    /** @param array<string, mixed> $headers */
    public function __construct(
        public readonly int $statusCode,
        public readonly string $body,
        public readonly array $headers = [],
    ) {
    }
}
