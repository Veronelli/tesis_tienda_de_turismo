<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Interfaces\Http\Controllers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use TiendaTurismo\GestionDatos\Application\Services\ConsultaService;
use TiendaTurismo\GestionDatos\Infrastructure\Security\JwtService;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\ConsultaController;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\ConsultaServiceMockTrait;

final class ConsultaControllerTest extends TestCase
{
    use ConsultaServiceMockTrait;

    private ConsultaService $consultaService;
    private ConsultaController $controller;
    private string $tokenValido;

    protected function setUp(): void
    {
        $_ENV['JWT_SECRET'] = 'test_secret';
        $_ENV['JWT_TTL'] = '3600';

        $this->consultaService = $this->createConsultaServiceMock();

        $jwt = new JwtService();
        $this->tokenValido = $jwt->encode(['sub' => 1, 'email' => 'admin@test.com', 'rol' => 'admin']);

        $this->controller = new ConsultaController($this->consultaService, $jwt);
    }

    public function test_crear_retorna_201(): void
    {
        $this->consultaService
            ->method('crear')
            ->willReturn([
                'id' => 1,
                'cliente' => ['id' => 1, 'nombre' => 'Juan', 'apellido' => 'Pérez', 'email' => 'juan@test.com'],
                'paquete' => ['id' => 1, 'nombre' => 'Paquete Test'],
                'mensaje' => 'Quiero información.',
                'estado' => 'pendiente',
            ]);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'paquete_id' => 1,
                'mensaje' => 'Quiero información.',
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'email' => 'juan@test.com',
                'telefono' => '123456789',
                'dni' => '12345678',
                'ubicacion' => 'Buenos Aires',
            ]),
        );

        $response = $this->controller->crear($request);

        $this->assertSame(201, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('Quiero información.', $content['mensaje']);
        $this->assertSame('pendiente', $content['estado']);
    }

    public function test_crear_con_datos_invalidos_retorna_422(): void
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'paquete_id' => 1,
            ]),
        );

        $response = $this->controller->crear($request);

        $this->assertSame(422, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('El mensaje es requerido.', $content['error']);
    }

    public function test_crear_con_calificacion_retorna_422(): void
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'paquete_id' => 1,
                'mensaje' => 'Mensaje de prueba',
                'calificacion' => 'Frio',
                'cliente_id' => 1,
            ]),
        );

        $response = $this->controller->crear($request);

        $this->assertSame(422, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('La calificación del lead no puede ser enviada por el cliente.', $content['error']);
    }

    public function test_crear_sin_paquete_id_retorna_422(): void
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'mensaje' => 'Mensaje de prueba',
                'cliente_id' => 1,
            ]),
        );

        $response = $this->controller->crear($request);

        $this->assertSame(422, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('El ID del paquete es requerido.', $content['error']);
    }

    public function test_crear_sin_cliente_ni_datos_retorna_422(): void
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'paquete_id' => 1,
                'mensaje' => 'Mensaje sin cliente',
            ]),
        );

        $response = $this->controller->crear($request);

        $this->assertSame(422, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('Debe proporcionar un cliente_id o datos del cliente (nombre, apellido, email).', $content['error']);
    }

    public function test_crear_con_json_invalido_retorna_400(): void
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json',
        );

        $response = $this->controller->crear($request);

        $this->assertSame(400, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('Datos inválidos.', $content['error']);
    }

    public function test_listar_retorna_consultas(): void
    {
        $this->consultaService
            ->method('listar')
            ->with([])
            ->willReturn([[
                'id' => 1,
                'cliente' => ['id' => 1, 'nombre' => 'Juan', 'apellido' => 'Pérez', 'email' => 'juan@test.com'],
                'paquete' => ['id' => 1, 'nombre' => 'Paquete Test'],
                'mensaje' => 'Test',
                'estado' => 'pendiente',
            ]]);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->tokenValido],
        );

        $response = $this->controller->listar($request);

        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertSame('pendiente', $content[0]['estado']);
    }

    public function test_listar_filtra_por_calificacion(): void
    {
        $this->consultaService
            ->expects($this->once())
            ->method('listar')
            ->with(['calificacion' => 'caliente'])
            ->willReturn([]);

        $request = new Request(
            ['calificacion' => 'Caliente'],
            [],
            [],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->tokenValido],
        );

        $response = $this->controller->listar($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_listar_sin_token_retorna_401(): void
    {
        $request = new Request();
        $response = $this->controller->listar($request);

        $this->assertSame(401, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('Token de autenticación requerido.', $content['error']);
    }

    public function test_obtenerPorId_retorna_consulta(): void
    {
        $this->consultaService
            ->method('obtenerPorId')
            ->with(1)
            ->willReturn([
                'id' => 1,
                'cliente' => ['id' => 1, 'nombre' => 'Juan'],
                'paquete' => ['id' => 1, 'nombre' => 'Paquete Test'],
                'mensaje' => 'Test',
                'estado' => 'pendiente',
            ]);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->tokenValido],
        );

        $response = $this->controller->obtenerPorId($request, ['id' => '1']);

        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('pendiente', $content['estado']);
    }

    public function test_obtenerPorId_retorna_404_si_no_existe(): void
    {
        $this->consultaService
            ->method('obtenerPorId')
            ->with(999)
            ->willReturn(null);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->tokenValido],
        );

        $response = $this->controller->obtenerPorId($request, ['id' => '999']);

        $this->assertSame(404, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('Consulta no encontrada.', $content['error']);
    }

    public function test_actualizar_retorna_200(): void
    {
        $this->consultaService
            ->method('actualizar')
            ->willReturn([
                'id' => 1,
                'cliente' => ['id' => 1],
                'paquete' => ['id' => 1],
                'mensaje' => 'Actualizado',
                'estado' => 'procesando',
            ]);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->tokenValido,
            ],
            json_encode(['estado' => 'procesando']),
        );

        $response = $this->controller->actualizar($request, ['id' => '1']);

        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('procesando', $content['estado']);
    }

    public function test_actualizar_con_calificacion_retorna_422(): void
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->tokenValido,
            ],
            json_encode(['estado' => 'procesando', 'calificacion' => 'Caliente']),
        );

        $response = $this->controller->actualizar($request, ['id' => '1']);

        $this->assertSame(422, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('La calificación del lead no puede ser enviada por el cliente.', $content['error']);
    }

}
