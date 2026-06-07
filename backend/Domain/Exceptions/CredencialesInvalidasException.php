<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Exceptions;

final class CredencialesInvalidasException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('No Authorized');
    }
}
