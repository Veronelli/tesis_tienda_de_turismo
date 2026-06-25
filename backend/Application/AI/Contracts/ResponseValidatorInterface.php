<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\AI\Contracts;

interface ResponseValidatorInterface
{
    /** @return array<string, mixed> */
    public function validate(string $responseText): array;
}
