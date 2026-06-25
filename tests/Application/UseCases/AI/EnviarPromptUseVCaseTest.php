<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\AI;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\AI\Contracts\GenerativeTextProviderInterface;
use TiendaTurismo\GestionDatos\Application\AI\DTO\AiPrompt;
use TiendaTurismo\GestionDatos\Application\AI\DTO\AiResponse;
use TiendaTurismo\GestionDatos\Application\UseCases\AI\EnviarPromptUseVCase;

final class EnviarPromptUseVCaseTest extends TestCase
{
    public function test_execute_delega_en_el_provider(): void
    {
        $provider = $this->createMock(GenerativeTextProviderInterface::class);
        $useCase = new EnviarPromptUseVCase($provider);

        $prompt = new AiPrompt(
            instructions: 'Actua como agente de ventas.',
            input: 'Hola, quiero viajar a Brasil.',
        );

        $expected = new AiResponse(
            text: 'Respuesta generada.',
            provider: 'openai',
            model: 'gpt-5.5',
        );

        $provider
            ->expects($this->once())
            ->method('generate')
            ->with($prompt)
            ->willReturn($expected);

        $result = $useCase->execute($prompt);

        $this->assertSame($expected, $result);
    }
}
