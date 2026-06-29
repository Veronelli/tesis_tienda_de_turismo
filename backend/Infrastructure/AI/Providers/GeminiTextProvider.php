<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\AI\Providers;

use TiendaTurismo\GestionDatos\Application\AI\Contracts\GenerativeTextProviderInterface;
use TiendaTurismo\GestionDatos\Application\AI\Contracts\HttpTransportInterface;
use TiendaTurismo\GestionDatos\Application\AI\DTO\AiPrompt;
use TiendaTurismo\GestionDatos\Application\AI\DTO\AiResponse;
use TiendaTurismo\GestionDatos\Application\AI\DTO\HttpRequest;
use TiendaTurismo\GestionDatos\Application\AI\DTO\HttpResponse;
use TiendaTurismo\GestionDatos\Application\AI\Exceptions\AiProviderException;

final class GeminiTextProvider implements GenerativeTextProviderInterface
{
    public function __construct(
        private readonly HttpTransportInterface $transport,
        private readonly string $apiKey,
        private readonly string $model = 'gemini-3.1-flash-lite',
        private readonly string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta',
        private readonly int $maxRetries = 3,
        private readonly int $retryDelayMilliseconds = 250,
    ) {
    }

    public function generate(AiPrompt $prompt): AiResponse
    {
        $input = $this->buildInput($prompt);

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
                        ['text' => $input],
                    ],
                ],
            ],
        ];

        if ($prompt->temperature !== null) {
            $payload['generationConfig'] = [
                'temperature' => $prompt->temperature,
            ];
        }

        $response = $this->sendWithRetry(new HttpRequest(
            method: 'POST',
            url: $this->baseUrl . '/models/' . rawurlencode($this->model) . ':generateContent?key=' . rawurlencode($this->apiKey),
            body: json_encode($payload, JSON_THROW_ON_ERROR),
        ));

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

    private function buildInput(AiPrompt $prompt): string
    {
        if (trim($prompt->context) === '') {
            return $prompt->input;
        }

        return "Contexto de ejecucion:\n{$prompt->context}\n\nMensaje:\n{$prompt->input}";
    }

    private function sendWithRetry(HttpRequest $request): HttpResponse
    {
        $attempt = 0;

        while (true) {
            $attempt++;
            $response = $this->transport->send($request);

            if ($response->statusCode >= 200 && $response->statusCode < 300) {
                return $response;
            }

            if (!in_array($response->statusCode, [429, 503], true) || $attempt >= $this->maxRetries) {
                throw new AiProviderException(
                    'Gemini respondió con código HTTP ' . $response->statusCode . '. ' . $this->extraerMensajeError($response->body),
                    $response->statusCode,
                );
            }

            usleep($this->retryDelayMilliseconds * 1000 * $attempt);
        }
    }

    private function extraerMensajeError(string $body): string
    {
        $data = json_decode($body, true);
        if (!is_array($data)) {
            return '';
        }

        $mensaje = $data['error']['message'] ?? '';
        return is_string($mensaje) && $mensaje !== '' ? 'Detalle: ' . $mensaje : '';
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
