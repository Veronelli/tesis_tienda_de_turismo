<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Infrastructure\AI\Providers;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\AI\Contracts\HttpTransportInterface;
use TiendaTurismo\GestionDatos\Application\AI\DTO\AiPrompt;
use TiendaTurismo\GestionDatos\Application\AI\DTO\HttpRequest;
use TiendaTurismo\GestionDatos\Application\AI\DTO\HttpResponse;
use TiendaTurismo\GestionDatos\Infrastructure\AI\Providers\OpenAiTextProvider;

final class OpenAiTextProviderTest extends TestCase
{
    public function test_generate_envia_prompt_inicial_a_openai(): void
    {
        $transport = $this->createMock(HttpTransportInterface::class);
        $provider = new OpenAiTextProvider($transport, 'openai-test-key', 'gpt-5.5');

        $prompt = new AiPrompt(
            instructions: 'Eres un agente de turismo.',
            input: 'Responde con una recomendacion breve.',
            temperature: 0.3,
            context: 'consulta_id=12',
        );

        $transport
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (HttpRequest $request) {
                $this->assertSame('POST', $request->method);
                $this->assertSame('https://api.openai.com/v1/responses', $request->url);
                $this->assertContains('Authorization: Bearer openai-test-key', $request->headers);

                $body = json_decode($request->body, true);
                $this->assertSame('gpt-5.5', $body['model']);
                $this->assertSame('Eres un agente de turismo.', $body['instructions']);
                $this->assertSame("Contexto de ejecucion:\nconsulta_id=12\n\nMensaje:\nResponde con una recomendacion breve.", $body['input']);
                $this->assertSame(0.3, $body['temperature']);

                return true;
            }))
            ->willReturn(new HttpResponse(
                statusCode: 200,
                body: json_encode([
                    'output_text' => 'Respuesta de OpenAI.',
                ], JSON_THROW_ON_ERROR),
            ));

        $response = $provider->generate($prompt);

        $this->assertSame('Respuesta de OpenAI.', $response->text);
        $this->assertSame('openai', $response->provider);
        $this->assertSame('gpt-5.5', $response->model);
    }
}
