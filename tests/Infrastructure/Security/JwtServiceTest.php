<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Infrastructure\Security;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Infrastructure\Security\JwtService;

final class JwtServiceTest extends TestCase
{
    private JwtService $jwt;

    protected function setUp(): void
    {
        $_ENV['JWT_SECRET'] = 'test_secret_key';
        $_ENV['JWT_TTL'] = '3600';

        $this->jwt = new JwtService();
    }

    public function test_encode_retorna_string_con_tres_segmentos(): void
    {
        $token = $this->jwt->encode(['sub' => 1, 'email' => 'test@test.com', 'rol' => 'admin']);

        $parts = explode('.', $token);

        $this->assertCount(3, $parts);
    }

    public function test_header_contiene_algorithmo_y_tipo(): void
    {
        $token = $this->jwt->encode(['sub' => 1, 'email' => 'a@b.com', 'rol' => 'lector']);

        $parts = explode('.', $token);
        $header = json_decode(base64_decode(strtr($parts[0], '-_', '+/')), true);

        $this->assertSame('HS256', $header['alg']);
        $this->assertSame('JWT', $header['typ']);
    }

    public function test_payload_contiene_los_datos_pasados(): void
    {
        $token = $this->jwt->encode(['sub' => 42, 'email' => 'a@b.com', 'rol' => 'editor']);

        $parts = explode('.', $token);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        $this->assertSame(42, $payload['sub']);
        $this->assertSame('a@b.com', $payload['email']);
        $this->assertSame('editor', $payload['rol']);
    }

    public function test_encode_agrega_iat_y_exp_por_defecto(): void
    {
        $token = $this->jwt->encode(['sub' => 1, 'email' => 'x@y.com', 'rol' => 'lector']);

        $parts = explode('.', $token);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        $this->assertArrayHasKey('iat', $payload);
        $this->assertArrayHasKey('exp', $payload);
        $this->assertSame($payload['iat'] + 3600, $payload['exp']);
    }

    public function test_payload_iat_personalizado_no_se_sobrescribe(): void
    {
        $token = $this->jwt->encode([
            'sub' => 1,
            'email' => 'x@y.com',
            'rol' => 'admin',
            'iat' => 1000,
            'exp' => 2000,
        ]);

        $parts = explode('.', $token);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        $this->assertSame(1000, $payload['iat']);
        $this->assertSame(2000, $payload['exp']);
    }

    public function test_dos_tokens_con_mismo_payload_son_distintos_por_iat(): void
    {
        $token1 = $this->jwt->encode(['sub' => 1, 'email' => 'a@a.com', 'rol' => 'admin']);
        sleep(1);
        $token2 = $this->jwt->encode(['sub' => 1, 'email' => 'a@a.com', 'rol' => 'admin']);

        $this->assertNotSame($token1, $token2);
    }

    public function test_secrets_distintos_producen_firmas_distintas(): void
    {
        $_ENV['JWT_SECRET'] = 'primer_secreto';
        $jwt1 = new JwtService();
        $token1 = $jwt1->encode(['sub' => 1, 'email' => 'a@a.com', 'rol' => 'admin', 'iat' => 100, 'exp' => 200]);

        $_ENV['JWT_SECRET'] = 'segundo_secreto';
        $jwt2 = new JwtService();
        $token2 = $jwt2->encode(['sub' => 1, 'email' => 'a@a.com', 'rol' => 'admin', 'iat' => 100, 'exp' => 200]);

        $parts1 = explode('.', $token1);
        $parts2 = explode('.', $token2);

        $this->assertSame($parts1[0], $parts2[0], 'header debe ser igual');
        $this->assertSame($parts1[1], $parts2[1], 'payload debe ser igual');
        $this->assertNotSame($parts1[2], $parts2[2], 'firma debe ser distinta');
    }

    public function test_token_con_caracteres_especiales_en_payload(): void
    {
        $token = $this->jwt->encode([
            'sub' => 1,
            'email' => 'user+tag@example.com',
            'rol' => 'admin',
        ]);

        $parts = explode('.', $token);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        $this->assertSame('user+tag@example.com', $payload['email']);
    }

    public function test_ttl_personalizado_desde_env(): void
    {
        $_ENV['JWT_TTL'] = '7200';
        $jwt = new JwtService();

        $token = $jwt->encode(['sub' => 1, 'email' => 'a@b.com', 'rol' => 'lector']);

        $parts = explode('.', $token);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        $this->assertSame($payload['iat'] + 7200, $payload['exp']);
    }

    public function test_firma_es_valida_hmac_sha256(): void
    {
        $token = $this->jwt->encode([
            'sub' => 5,
            'email' => 'validar@test.com',
            'rol' => 'editor',
            'iat' => 100,
            'exp' => 200,
        ]);

        $parts = explode('.', $token);

        $firmaEsperada = self::base64urlEncode(
            hash_hmac('sha256', "{$parts[0]}.{$parts[1]}", 'test_secret_key', true),
        );

        $this->assertSame($firmaEsperada, $parts[2]);
    }

    private static function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
