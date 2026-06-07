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

    public function test_encode_payload_contiene_los_datos(): void
    {
        $token = $this->jwt->encode(['sub' => 42, 'email' => 'a@b.com', 'rol' => 'editor']);

        $parts = explode('.', $token);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        $this->assertSame(42, $payload['sub']);
        $this->assertSame('a@b.com', $payload['email']);
        $this->assertSame('editor', $payload['rol']);
    }

    public function test_encode_agrega_iat_y_exp(): void
    {
        $token = $this->jwt->encode(['sub' => 1, 'email' => 'x@y.com', 'rol' => 'lector']);

        $parts = explode('.', $token);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        $this->assertArrayHasKey('iat', $payload);
        $this->assertArrayHasKey('exp', $payload);
        $this->assertSame($payload['iat'] + 3600, $payload['exp']);
    }

    public function test_dos_tokens_con_mismo_payload_son_distintos_por_iat(): void
    {
        $token1 = $this->jwt->encode(['sub' => 1, 'email' => 'a@a.com', 'rol' => 'admin']);
        sleep(1);
        $token2 = $this->jwt->encode(['sub' => 1, 'email' => 'a@a.com', 'rol' => 'admin']);

        $this->assertNotSame($token1, $token2);
    }
}
