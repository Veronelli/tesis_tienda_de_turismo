<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Interfaces\Http\Controllers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use TiendaTurismo\GestionDatos\Application\Services\ClienteService;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\ClienteController;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\ClienteServiceMockTrait;

final class ClienteControllerTest extends TestCase
{
    use ClienteServiceMockTrait;

    private ClienteService $clienteService;
    private ClienteController $controller;

    protected function setUp(): void
    {
        $this->clienteService = $this->createClienteServiceMock();
        $this->controller = new ClienteController($this->clienteService);
    }

    public function test_listar_retorna_clientes(): void
    {
        $this->clienteService
            ->method('listar')
            ->willReturn([['id' => 1, 'nombre' => 'Juan', 'apellido' => 'Pérez']]);

        $request = new Request();
        $response = $this->controller->listar($request);

        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertSame('Juan', $content[0]['nombre']);
    }

    public function test_crear_retorna_201(): void
    {
        $this->clienteService
            ->method('crear')
            ->willReturn(['id' => 1, 'nombre' => 'Juan', 'apellido' => 'Pérez']);

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'telefono' => '123456789',
            'dni' => '12345678',
            'ubicacion' => 'Buenos Aires',
        ]));

        $response = $this->controller->crear($request);

        $this->assertSame(201, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Juan', $content['nombre']);
    }

    public function test_crear_con_datos_invalidos_retorna_400(): void
    {
        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], 'invalid json');

        $response = $this->controller->crear($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_crear_con_servicio_null_retorna_404(): void
    {
        $this->clienteService
            ->method('crear')
            ->willThrowException(new \RuntimeException('Cliente no encontrado.'));

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'telefono' => '123456789',
            'dni' => '12345678',
            'ubicacion' => 'Buenos Aires',
        ]));

        $response = $this->controller->crear($request);

        $this->assertSame(404, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Cliente no encontrado.', $content['error']);
    }

    public function test_crear_con_email_duplicado_retorna_409(): void
    {
        $this->clienteService
            ->method('crear')
            ->willThrowException(new \TiendaTurismo\GestionDatos\Domain\Exceptions\DuplicadoException('Ya existe un cliente con ese email.'));

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'telefono' => '123456789',
            'dni' => '12345678',
            'ubicacion' => 'Buenos Aires',
        ]));

        $response = $this->controller->crear($request);

        $this->assertSame(409, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Ya existe un cliente con ese email.', $content['error']);
    }

    public function test_actualizar_con_email_duplicado_retorna_409(): void
    {
        $this->clienteService
            ->method('actualizar')
            ->willThrowException(new \TiendaTurismo\GestionDatos\Domain\Exceptions\DuplicadoException('Ya existe un cliente con ese email.'));

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'duplicado@example.com',
            'telefono' => '123456789',
            'dni' => '12345678',
            'ubicacion' => 'Buenos Aires',
        ]));

        $response = $this->controller->actualizar($request, ['id' => '2']);

        $this->assertSame(409, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Ya existe un cliente con ese email.', $content['error']);
    }

    public function test_actualizar_retorna_200(): void
    {
        $this->clienteService
            ->method('actualizar')
            ->willReturn(['id' => 1, 'nombre' => 'Ana', 'apellido' => 'López']);

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nombre' => 'Ana',
            'apellido' => 'López',
            'email' => 'ana@example.com',
            'telefono' => '999',
            'dni' => '888',
            'ubicacion' => 'Córdoba',
        ]));

        $response = $this->controller->actualizar($request, ['id' => '1']);

        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Ana', $content['nombre']);
    }

    public function test_actualizar_cliente_inexistente_retorna_404(): void
    {
        $this->clienteService
            ->method('actualizar')
            ->willThrowException(new \RuntimeException('Cliente no encontrado.'));

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nombre' => 'Ana',
            'apellido' => 'López',
            'email' => 'ana@example.com',
            'telefono' => '999',
            'dni' => '888',
            'ubicacion' => 'Córdoba',
        ]));

        $response = $this->controller->actualizar($request, ['id' => '999']);

        $this->assertSame(404, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Cliente no encontrado.', $content['error']);
    }

    public function test_actualizar_con_datos_invalidos_retorna_400(): void
    {
        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], 'invalid');

        $response = $this->controller->actualizar($request, ['id' => '1']);

        $this->assertSame(400, $response->getStatusCode());
    }
}
