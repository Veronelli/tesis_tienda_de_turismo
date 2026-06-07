<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\Security;

final class JwtService
{
    private string $secret;
    private int $ttl;

    public function __construct()
    {
        $this->secret = $_ENV['JWT_SECRET'] ?? 'default_secret_change_me';
        $this->ttl = (int) ($_ENV['JWT_TTL'] ?? 3600);
    }

    /** @param array<string, mixed> $payload */
    public function encode(array $payload): string
    {
        $header = self::base64urlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));

        $payload['iat'] = $payload['iat'] ?? time();
        $payload['exp'] = $payload['exp'] ?? time() + $this->ttl;

        $payloadEncoded = self::base64urlEncode(json_encode($payload, JSON_THROW_ON_ERROR));

        $signature = self::base64urlEncode(
            hash_hmac('sha256', "{$header}.{$payloadEncoded}", $this->secret, true),
        );

        return "{$header}.{$payloadEncoded}.{$signature}";
    }

    private static function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
