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

    /** @return array<string, mixed>|null */
    public function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;

        $expectedSignature = self::base64urlEncode(
            hash_hmac('sha256', "{$header}.{$payload}", $this->secret, true),
        );

        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $data = json_decode(self::base64urlDecode($payload), true);

        if (!is_array($data) || !isset($data['exp'])) {
            return null;
        }

        if ($data['exp'] < time()) {
            return null;
        }

        return $data;
    }

    private static function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
