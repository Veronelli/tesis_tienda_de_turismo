<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\AI;

use TiendaTurismo\GestionDatos\Application\AI\Contracts\GenerativeTextProviderInterface;
use TiendaTurismo\GestionDatos\Application\AI\Exceptions\AiProviderException;
use TiendaTurismo\GestionDatos\Infrastructure\AI\Providers\GeminiTextProvider;
use TiendaTurismo\GestionDatos\Infrastructure\AI\Providers\OpenAiTextProvider;
use TiendaTurismo\GestionDatos\Infrastructure\AI\Transport\CurlHttpTransport;

final class TextGenerationProviderFactory
{
    public static function createFromEnv(): GenerativeTextProviderInterface
    {
        $transport = new CurlHttpTransport((int) ($_ENV['AI_TIMEOUT_SECONDS'] ?? 30));
        $provider = strtolower((string) ($_ENV['AI_PROVIDER'] ?? 'openai'));

        return match ($provider) {
            'gemini' => self::crearGemini($transport),
            'openai' => self::crearOpenAi($transport),
            default => throw new AiProviderException('AI_PROVIDER debe ser openai o gemini.'),
        };
    }

    private static function crearGemini(CurlHttpTransport $transport): GenerativeTextProviderInterface
    {
        $apiKey = trim((string) ($_ENV['GEMINI_API_KEY'] ?? ''));
        if ($apiKey === '') {
            throw new AiProviderException('GEMINI_API_KEY es requerida.');
        }

        return new GeminiTextProvider(
            transport: $transport,
            apiKey: $apiKey,
            model: (string) ($_ENV['GEMINI_MODEL'] ?? 'gemini-2.0-flash'),
        );
    }

    private static function crearOpenAi(CurlHttpTransport $transport): GenerativeTextProviderInterface
    {
        $apiKey = trim((string) ($_ENV['OPENAI_API_KEY'] ?? ''));
        if ($apiKey === '') {
            throw new AiProviderException('OPENAI_API_KEY es requerida.');
        }

        return new OpenAiTextProvider(
            transport: $transport,
            apiKey: $apiKey,
            model: (string) ($_ENV['OPENAI_MODEL'] ?? 'gpt-5.5'),
        );
    }
}
