<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\AI\Providers;

use TiendaTurismo\GestionDatos\Application\AI\Contracts\GenerativeTextProviderInterface;
use TiendaTurismo\GestionDatos\Application\AI\Contracts\HttpTransportInterface;
use TiendaTurismo\GestionDatos\Application\AI\DTO\AiPrompt;
use TiendaTurismo\GestionDatos\Application\AI\DTO\AiResponse;
use TiendaTurismo\GestionDatos\Application\AI\DTO\HttpRequest;
use TiendaTurismo\GestionDatos\Application\AI\Exceptions\AiProviderException;

final class OpenAiTextProvider implements GenerativeTextProviderInterface
{
    public function __construct(
        private readonly HttpTransportInterface $transport,
        private readonly string $apiKey,
        private readonly string $model = 'gpt-5.5',
        private readonly string $endpoint = 'https://api.openai.com/v1/responses',
    ) {
    }

    public function generate(AiPrompt $prompt): AiResponse
    {
        $payload = [
            'model' => $this->model,
            'instructions' => $prompt->instructions,
            'input' => $prompt->input,
        ];

        if ($prompt->temperature !== null) {
            $payload['temperature'] = $prompt->temperature;
        }

        $response = $this->transport->send(new HttpRequest(
            method: 'POST',
            url: $this->endpoint,
            headers: [
                'Authorization: Bearer ' . $this->apiKey,
            ],
            body: json_encode($payload, JSON_THROW_ON_ERROR),
        ));

        if ($response->statusCode < 200 || $response->statusCode >= 300) {
            throw new AiProviderException('OpenAI respondió con código HTTP ' . $response->statusCode . '.');
        }

        $data = json_decode($response->body, true);
        if (!is_array($data)) {
            throw new AiProviderException('Respuesta inválida de OpenAI.');
        }

        $text = $data['output_text'] ?? $this->extraerTexto($data['output'] ?? []);
        if (!is_string($text) || trim($text) === '') {
            throw new AiProviderException('OpenAI no devolvió texto.');
        }

        return new AiResponse(
            text: $text,
            provider: 'openai',
            model: $this->model,
            raw: $data,
        );
    }

    /** @param array<int, mixed> $output */
    private function extraerTexto(array $output): string
    {
        foreach ($output as $item) {
            if (!is_array($item) || ($item['type'] ?? null) !== 'message') {
                continue;
            }

            $content = $item['content'] ?? [];
            if (!is_array($content)) {
                continue;
            }

            foreach ($content as $part) {
                if (is_array($part) && ($part['type'] ?? null) === 'output_text' && isset($part['text'])) {
                    return (string) $part['text'];
                }
            }
        }

        return '';
    }
}
