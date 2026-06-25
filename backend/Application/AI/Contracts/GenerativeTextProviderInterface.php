<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\AI\Contracts;

use TiendaTurismo\GestionDatos\Application\AI\DTO\AiPrompt;
use TiendaTurismo\GestionDatos\Application\AI\DTO\AiResponse;

interface GenerativeTextProviderInterface
{
    public function generate(AiPrompt $prompt): AiResponse;
}
