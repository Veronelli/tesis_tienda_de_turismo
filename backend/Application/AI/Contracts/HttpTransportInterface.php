<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\AI\Contracts;

use TiendaTurismo\GestionDatos\Application\AI\DTO\HttpRequest;
use TiendaTurismo\GestionDatos\Application\AI\DTO\HttpResponse;

interface HttpTransportInterface
{
    public function send(HttpRequest $request): HttpResponse;
}
