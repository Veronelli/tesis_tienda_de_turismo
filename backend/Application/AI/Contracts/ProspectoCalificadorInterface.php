<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\AI\Contracts;

interface ProspectoCalificadorInterface
{
    /** @return array{calificacion:string} */
    public function execute(string $mensaje): array;
}
