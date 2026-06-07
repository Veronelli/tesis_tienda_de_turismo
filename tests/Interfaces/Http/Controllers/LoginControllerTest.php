<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Interfaces\Http\Controllers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use TiendaTurismo\GestionDatos\Application\UseCases\Usuario\LoginUseCase;
use TiendaTurismo\GestionDatos\Domain\Exceptions\CredencialesInvalidasException;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\LoginController;

final class LoginControllerTest extends TestCase
{
    private LoginUseCase $loginUseCase;
    private LoginController $controller;

    protected function setUp(): void
    {
        $this->loginUseCase = $this->createMock(LoginUseCase::class);
        $this->controller = new LoginController($this->loginUseCase);
    }

    public function test_login_exitoso_retorna_token(): void
    {
        $this->loginUseCase
            ->method('execute')
            ->with('user@test.com', 'pass123')
            ->willReturn('token_generado.abc.def');

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'user@test.com',
            'password' => 'pass123',
        ]));

        $response = $this->controller->login($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $content = json_decode($response->getContent(), true);
        $this->assertSame('token_generado.abc.def', $content['token']);
    }

    public function test_login_con_credenciales_invalidas_retorna_401(): void
    {
        $this->loginUseCase
            ->method('execute')
            ->with('mal@email.com', 'badpass')
            ->willThrowException(new CredencialesInvalidasException());

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'mal@email.com',
            'password' => 'badpass',
        ]));

        $response = $this->controller->login($request);

        $this->assertSame(401, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertSame('No Authorized', $content['error']);
    }

    public function test_login_sin_email_retorna_401(): void
    {
        $this->loginUseCase
            ->method('execute')
            ->with('', 'pass')
            ->willThrowException(new CredencialesInvalidasException());

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'password' => 'pass',
        ]));

        $response = $this->controller->login($request);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_login_sin_password_retorna_401(): void
    {
        $this->loginUseCase
            ->method('execute')
            ->with('user@test.com', '')
            ->willThrowException(new CredencialesInvalidasException());

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'user@test.com',
        ]));

        $response = $this->controller->login($request);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_login_con_cuerpo_vacio_retorna_401(): void
    {
        $this->loginUseCase
            ->method('execute')
            ->with('', '')
            ->willThrowException(new CredencialesInvalidasException());

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));

        $response = $this->controller->login($request);

        $this->assertSame(401, $response->getStatusCode());
    }
}
