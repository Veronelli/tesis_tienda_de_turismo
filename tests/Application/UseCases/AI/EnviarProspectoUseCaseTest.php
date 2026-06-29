<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\AI;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\AI\Contracts\GenerativeTextProviderInterface;
use TiendaTurismo\GestionDatos\Application\AI\Contracts\PromptBuilderInterface;
use TiendaTurismo\GestionDatos\Application\AI\Contracts\ResponseValidatorInterface;
use TiendaTurismo\GestionDatos\Application\AI\DTO\AiPrompt;
use TiendaTurismo\GestionDatos\Application\AI\DTO\AiResponse;
use TiendaTurismo\GestionDatos\Application\UseCases\AI\EnviarProspectoUseCase;

final class EnviarProspectoUseCaseTest extends TestCase
{
    public function test_execute_envia_prompt_y_valida_respuesta(): void
    {
        $promptBuilder = $this->createMock(PromptBuilderInterface::class);
        $provider = $this->createMock(GenerativeTextProviderInterface::class);
        $validator = $this->createMock(ResponseValidatorInterface::class);
        $useCase = new EnviarProspectoUseCase($promptBuilder, $provider, $validator);

        $prompt = new AiPrompt(
            instructions: 'Actua como agente de ventas.',
            input: 'Hola, quiero viajar a Brasil.',
            context: 'Ejecucion de prueba',
        );

        $response = new AiResponse(
            text: '{"calificacion":"CALIENTE"}',
            provider: 'openai',
            model: 'gpt-5.5',
        );

        $promptBuilder
            ->expects($this->once())
            ->method('build')
            ->with('Hola, quiero viajar a Brasil.', 'Ejecucion de prueba')
            ->willReturn($prompt);

        $provider
            ->expects($this->once())
            ->method('generate')
            ->with($prompt)
            ->willReturn($response);

        $validator
            ->expects($this->once())
            ->method('validate')
            ->with('{"calificacion":"CALIENTE"}')
            ->willReturn(['calificacion' => 'CALIENTE']);

        $result = $useCase->execute('Hola, quiero viajar a Brasil.', 'Ejecucion de prueba');

        $this->assertSame(['calificacion' => 'CALIENTE'], $result);
    }
}
