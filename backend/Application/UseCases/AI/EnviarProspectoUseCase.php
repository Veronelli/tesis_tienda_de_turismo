<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\AI;

use TiendaTurismo\GestionDatos\Application\AI\Contracts\GenerativeTextProviderInterface;
use TiendaTurismo\GestionDatos\Application\AI\Contracts\PromptBuilderInterface;
use TiendaTurismo\GestionDatos\Application\AI\Contracts\ResponseValidatorInterface;
use TiendaTurismo\GestionDatos\Application\AI\Contracts\ProspectoCalificadorInterface;

final class EnviarProspectoUseCase implements ProspectoCalificadorInterface
{
    public function __construct(
        private readonly PromptBuilderInterface $promptBuilder,
        private readonly GenerativeTextProviderInterface $provider,
        private readonly ResponseValidatorInterface $validator,
    ) {
    }

    /** @return array{calificacion:string} */
    public function execute(string $mensaje): array
    {
        $prompt = $this->promptBuilder->build($mensaje);
        $response = $this->provider->generate($prompt);

        return $this->validator->validate($response->text);
    }
}
