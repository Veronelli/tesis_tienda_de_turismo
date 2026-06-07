<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Usuario;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Usuario\LoginUseCase;
use TiendaTurismo\GestionDatos\Domain\Exceptions\CredencialesInvalidasException;
use TiendaTurismo\GestionDatos\Domain\Models\Usuario;
use TiendaTurismo\GestionDatos\Domain\Repositories\UsuarioRepositoryInterface;
use TiendaTurismo\GestionDatos\Infrastructure\Security\JwtService;

final class LoginUseCaseTest extends TestCase
{
    private UsuarioRepositoryInterface $usuarios;
    private JwtService $jwt;
    private LoginUseCase $loginUseCase;

    protected function setUp(): void
    {
        $_ENV['JWT_SECRET'] = 'test_secret';
        $_ENV['JWT_TTL'] = '3600';

        $this->usuarios = $this->createMock(UsuarioRepositoryInterface::class);
        $this->jwt = new JwtService();
        $this->loginUseCase = new LoginUseCase($this->usuarios, $this->jwt);
    }

    public function test_login_exitoso_retorna_jwt_con_datos_del_usuario(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);

        $usuario = new Usuario(
            nombre: 'Juan',
            apellido: 'Pérez',
            numeroDocumento: 'DNI12345678',
            email: 'juan@example.com',
            contrasena: $hash,
            rol: 'admin',
            id: 1,
        );

        $this->usuarios
            ->method('findByEmail')
            ->with('juan@example.com')
            ->willReturn($usuario);

        $token = $this->loginUseCase->execute('juan@example.com', 'password123');

        $this->assertIsString($token);

        $parts = explode('.', $token);
        $this->assertCount(3, $parts);

        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        $this->assertSame(1, $payload['sub']);
        $this->assertSame('juan@example.com', $payload['email']);
        $this->assertSame('admin', $payload['rol']);
        $this->assertArrayHasKey('iat', $payload);
        $this->assertArrayHasKey('exp', $payload);
    }

    public function test_token_generado_para_rol_lector(): void
    {
        $hash = password_hash('pass', PASSWORD_BCRYPT);

        $usuario = new Usuario(
            nombre: 'Lector',
            apellido: 'Test',
            numeroDocumento: 'DNI11111111',
            email: 'lector@test.com',
            contrasena: $hash,
            rol: 'lector',
            id: 2,
        );

        $this->usuarios
            ->method('findByEmail')
            ->willReturn($usuario);

        $token = $this->loginUseCase->execute('lector@test.com', 'pass');
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        $this->assertSame('lector', $payload['rol']);
    }

    public function test_token_generado_para_rol_editor(): void
    {
        $hash = password_hash('pass', PASSWORD_BCRYPT);

        $usuario = new Usuario(
            nombre: 'Editor',
            apellido: 'Test',
            numeroDocumento: 'DNI22222222',
            email: 'editor@test.com',
            contrasena: $hash,
            rol: 'editor',
            id: 3,
        );

        $this->usuarios
            ->method('findByEmail')
            ->willReturn($usuario);

        $token = $this->loginUseCase->execute('editor@test.com', 'pass');
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        $this->assertSame('editor', $payload['rol']);
    }

    public function test_token_incluye_id_correcto_del_usuario(): void
    {
        $hash = password_hash('pass', PASSWORD_BCRYPT);

        $usuario = new Usuario(
            nombre: 'N',
            apellido: 'M',
            numeroDocumento: 'DNI33333333',
            email: 'id@test.com',
            contrasena: $hash,
            rol: 'admin',
            id: 99,
        );

        $this->usuarios
            ->method('findByEmail')
            ->willReturn($usuario);

        $token = $this->loginUseCase->execute('id@test.com', 'pass');
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        $this->assertSame(99, $payload['sub']);
    }

    public function test_login_con_email_incorrecto_lanza_excepcion(): void
    {
        $this->usuarios
            ->method('findByEmail')
            ->with('no@existe.com')
            ->willReturn(null);

        $this->expectException(CredencialesInvalidasException::class);
        $this->expectExceptionMessage('No Authorized');

        $this->loginUseCase->execute('no@existe.com', 'pass');
    }

    public function test_login_con_contrasena_incorrecta_lanza_excepcion(): void
    {
        $hash = password_hash('correcta', PASSWORD_BCRYPT);

        $usuario = new Usuario(
            nombre: 'A',
            apellido: 'B',
            numeroDocumento: 'DNI99999999',
            email: 'a@b.com',
            contrasena: $hash,
            rol: 'lector',
            id: 2,
        );

        $this->usuarios
            ->method('findByEmail')
            ->with('a@b.com')
            ->willReturn($usuario);

        $this->expectException(CredencialesInvalidasException::class);
        $this->expectExceptionMessage('No Authorized');

        $this->loginUseCase->execute('a@b.com', 'incorrecta');
    }

    public function test_login_con_email_vacio_lanza_excepcion(): void
    {
        $this->usuarios
            ->method('findByEmail')
            ->with('')
            ->willReturn(null);

        $this->expectException(CredencialesInvalidasException::class);

        $this->loginUseCase->execute('', 'pass');
    }

    public function test_login_exitoso_genera_token_con_firma_valida(): void
    {
        $hash = password_hash('segura', PASSWORD_BCRYPT);

        $usuario = new Usuario(
            nombre: 'Firma',
            apellido: 'Test',
            numeroDocumento: 'DNI44444444',
            email: 'firma@test.com',
            contrasena: $hash,
            rol: 'admin',
            id: 10,
        );

        $this->usuarios
            ->method('findByEmail')
            ->willReturn($usuario);

        $token = $this->loginUseCase->execute('firma@test.com', 'segura');
        $parts = explode('.', $token);

        $firmaEsperada = rtrim(strtr(base64_encode(
            hash_hmac('sha256', "{$parts[0]}.{$parts[1]}", 'test_secret', true),
        ), '+/', '-_'), '=');

        $this->assertSame($firmaEsperada, $parts[2]);
    }
}
