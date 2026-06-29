<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\AI\Contracts;

use TiendaTurismo\GestionDatos\Application\AI\DTO\AiPrompt;

interface PromptBuilderInterface
{
    public function build(string $input, string $context = ''): AiPrompt;
}
