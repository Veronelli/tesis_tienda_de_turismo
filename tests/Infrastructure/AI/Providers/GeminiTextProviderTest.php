<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Infrastructure\AI\Providers;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\AI\Contracts\HttpTransportInterface;
use TiendaTurismo\GestionDatos\Application\AI\DTO\AiPrompt;
use TiendaTurismo\GestionDatos\Application\AI\DTO\HttpRequest;
use TiendaTurismo\GestionDatos\Application\AI\DTO\HttpResponse;
use TiendaTurismo\GestionDatos\Infrastructure\AI\Providers\GeminiTextProvider;

final class GeminiTextProviderTest extends TestCase
{
    public function test_generate_envia_prompt_inicial_a_gemini(): void
    {
        $transport = $this->createMock(HttpTransportInterface::class);
        $provider = new GeminiTextProvider($transport, 'gemini-test-key', 'gemini-2.0-flash');

        $prompt = new AiPrompt(
            instructions: 'Eres un agente de turismo.',
            input: 'Dame una respuesta breve.',
            temperature: 0.6,
        );

        $transport
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (HttpRequest $request) {
                $this->assertSame('POST', $request->method);
                $this->assertStringContainsString(
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=gemini-test-key',
                    $request->url,
                );

                $body = json_decode($request->body, true);
                $this->assertSame('Eres un agente de turismo.', $body['systemInstruction']['parts'][0]['text']);
                $this->assertSame('Dame una respuesta breve.', $body['contents'][0]['parts'][0]['text']);
                $this->assertSame(0.6, $body['generationConfig']['temperature']);

                return true;
            }))
            ->willReturn(new HttpResponse(
                statusCode: 200,
                body: json_encode([
                    'candidates' => [[
                        'content' => [
                            'parts' => [[
                                'text' => 'Respuesta de Gemini.',
                            ]],
                        ],
                    ]],
                ], JSON_THROW_ON_ERROR),
            ));

        $response = $provider->generate($prompt);

        $this->assertSame('Respuesta de Gemini.', $response->text);
        $this->assertSame('gemini', $response->provider);
        $this->assertSame('gemini-2.0-flash', $response->model);
    }
}
