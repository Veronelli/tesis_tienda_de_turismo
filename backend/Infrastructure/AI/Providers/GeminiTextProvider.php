<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\AI\Providers;

use TiendaTurismo\GestionDatos\Application\AI\Contracts\GenerativeTextProviderInterface;
use TiendaTurismo\GestionDatos\Application\AI\Contracts\HttpTransportInterface;
use TiendaTurismo\GestionDatos\Application\AI\DTO\AiPrompt;
use TiendaTurismo\GestionDatos\Application\AI\DTO\AiResponse;
use TiendaTurismo\GestionDatos\Application\AI\DTO\HttpRequest;
use TiendaTurismo\GestionDatos\Application\AI\Exceptions\AiProviderException;

final class GeminiTextProvider implements GenerativeTextProviderInterface
{
    public function __construct(
        private readonly HttpTransportInterface $transport,
        private readonly string $apiKey,
        private readonly string $model = 'gemini-2.0-flash',
        private readonly string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta',
    ) {
    }

    public function generate(AiPrompt $prompt): AiResponse
    {
        $payload = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $prompt->instructions],
                ],
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt->input],
                    ],
                ],
            ],
        ];

        if ($prompt->temperature !== null) {
            $payload['generationConfig'] = [
                'temperature' => $prompt->temperature,
            ];
        }

        $response = $this->transport->send(new HttpRequest(
            method: 'POST',
            url: $this->baseUrl . '/models/' . rawurlencode($this->model) . ':generateContent?key=' . rawurlencode($this->apiKey),
            body: json_encode($payload, JSON_THROW_ON_ERROR),
        ));

        if ($response->statusCode < 200 || $response->statusCode >= 300) {
            throw new AiProviderException('Gemini respondió con código HTTP ' . $response->statusCode . '.');
        }

        $data = json_decode($response->body, true);
        if (!is_array($data)) {
            throw new AiProviderException('Respuesta inválida de Gemini.');
        }

        $text = $this->extraerTexto($data);
        if (trim($text) === '') {
            throw new AiProviderException('Gemini no devolvió texto.');
        }

        return new AiResponse(
            text: $text,
            provider: 'gemini',
            model: $this->model,
            raw: $data,
        );
    }

    /** @param array<string, mixed> $data */
    private function extraerTexto(array $data): string
    {
        $candidates = $data['candidates'] ?? [];
        if (!is_array($candidates)) {
            return '';
        }

        foreach ($candidates as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }

            $content = $candidate['content'] ?? null;
            if (!is_array($content)) {
                continue;
            }

            $parts = $content['parts'] ?? [];
            if (!is_array($parts)) {
                continue;
            }

            foreach ($parts as $part) {
                if (is_array($part) && isset($part['text'])) {
                    return (string) $part['text'];
                }
            }
        }

        return '';
    }
}
