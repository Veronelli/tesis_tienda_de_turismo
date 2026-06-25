<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\AI;

use TiendaTurismo\GestionDatos\Application\AI\Contracts\GenerativeTextProviderInterface;
use TiendaTurismo\GestionDatos\Application\AI\Contracts\ResponseValidatorInterface;
use TiendaTurismo\GestionDatos\Application\AI\DTO\AiPrompt;
use TiendaTurismo\GestionDatos\Application\AI\DTO\AiResponse;

final class EnviarPromptUseVCase
{
    public function __construct(
        private readonly GenerativeTextProviderInterface $provider,
        private readonly ?ResponseValidatorInterface $validator = null,
    ) {
    }

    public function execute(AiPrompt $prompt): AiResponse
    {
        $response = $this->provider->generate($prompt);

        if ($this->validator !== null) {
            $this->validator->validate($response->text);
        }

        return $response;
    }
}
