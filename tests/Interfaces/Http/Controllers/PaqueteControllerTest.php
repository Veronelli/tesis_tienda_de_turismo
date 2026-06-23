<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Interfaces\Http\Controllers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use TiendaTurismo\GestionDatos\Application\Services\PaqueteService;
use TiendaTurismo\GestionDatos\Infrastructure\Security\JwtService;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\PaqueteController;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\PaqueteServiceMockTrait;

final class PaqueteControllerTest extends TestCase
{
    use PaqueteServiceMockTrait;

    private PaqueteService $paqueteService;
    private PaqueteController $controller;
    private string $tokenValido;

    protected function setUp(): void
    {
        $_ENV['JWT_SECRET'] = 'test_secret';
        $_ENV['JWT_TTL'] = '3600';

        $this->paqueteService = $this->createPaqueteServiceMock();

        $jwt = new JwtService();
        $this->tokenValido = $jwt->encode(['sub' => 1, 'email' => 'admin@test.com', 'rol' => 'admin']);

        $this->controller = new PaqueteController($this->paqueteService, $jwt);
    }

    public function test_listar_retorna_paquetes(): void
    {
        $this->paqueteService
            ->method('listar')
            ->with([])
            ->willReturn([['id' => 1, 'nombre' => 'Paquete Test']]);

        $request = new Request();
        $response = $this->controller->listar($request);

        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertSame('Paquete Test', $content[0]['nombre']);
    }

    public function test_listar_con_filtro_nombre(): void
    {
        $this->paqueteService
            ->expects($this->once())
            ->method('listar')
            ->with(['nombre' => 'costa'])
            ->willReturn([['id' => 1, 'nombre' => 'Paquete Costa']]);

        $request = new Request(['nombre' => 'costa']);
        $response = $this->controller->listar($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_listar_con_filtro_mes_partida(): void
    {
        $this->paqueteService
            ->expects($this->once())
            ->method('listar')
            ->with(['mes_partida' => '7'])
            ->willReturn([]);

        $request = new Request(['mes_partida' => '7']);
        $response = $this->controller->listar($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_listar_con_filtro_destino(): void
    {
        $this->paqueteService
            ->expects($this->once())
            ->method('listar')
            ->with(['destino_id' => 1])
            ->willReturn([]);

        $request = new Request(['destino_id' => '1']);
        $response = $this->controller->listar($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_listar_con_orden_precio_asc(): void
    {
        $this->paqueteService
            ->expects($this->once())
            ->method('listar')
            ->with(['orden_precio' => 'asc'])
            ->willReturn([]);

        $request = new Request(['orden_precio' => 'asc']);
        $response = $this->controller->listar($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_listar_con_orden_precio_desc(): void
    {
        $this->paqueteService
            ->expects($this->once())
            ->method('listar')
            ->with(['orden_precio' => 'desc'])
            ->willReturn([]);

        $request = new Request(['orden_precio' => 'desc']);
        $response = $this->controller->listar($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_obtenerPorId_retorna_paquete(): void
    {
        $this->paqueteService
            ->method('obtenerPorId')
            ->with(1)
            ->willReturn(['id' => 1, 'nombre' => 'Paquete Test']);

        $request = new Request();
        $response = $this->controller->obtenerPorId($request, ['id' => '1']);

        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('Paquete Test', $content['nombre']);
    }

    public function test_obtenerPorId_retorna_404_si_no_existe(): void
    {
        $this->paqueteService
            ->method('obtenerPorId')
            ->with(999)
            ->willReturn(null);

        $request = new Request();
        $response = $this->controller->obtenerPorId($request, ['id' => '999']);

        $this->assertSame(404, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('Paquete no encontrado.', $content['error']);
    }

    public function test_crear_retorna_201(): void
    {
        $this->paqueteService
            ->method('crear')
            ->willReturn(['id' => 1, 'nombre' => 'Paquete Nuevo']);

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
            json_encode([
                'nombre' => 'Paquete Nuevo',
                'descripcion' => 'Descripción',
                'fecha_partida' => '2026-07-15',
                'fecha_vuelta' => '2026-07-22',
                'precio' => 1500.00,
                'disponible' => true,
                'hoteles_ids' => [1],
            ]),
        );

        $response = $this->controller->crear($request);

        $this->assertSame(201, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('Paquete Nuevo', $content['nombre']);
    }

    public function test_crear_con_datos_invalidos_retorna_400(): void
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
            'invalid json',
        );

        $response = $this->controller->crear($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_crear_sin_nombre_retorna_422(): void
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
            json_encode([
                'fecha_partida' => '2026-07-15',
                'precio' => 100,
                'hoteles_ids' => [1],
            ]),
        );

        $response = $this->controller->crear($request);

        $this->assertSame(422, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('El nombre es requerido.', $content['error']);
    }

    public function test_crear_con_precio_no_numerico_retorna_422(): void
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
            json_encode([
                'nombre' => 'Test',
                'fecha_partida' => '2026-07-15',
                'precio' => 'abc',
                'hoteles_ids' => [1],
            ]),
        );

        $response = $this->controller->crear($request);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_crear_sin_hoteles_retorna_422(): void
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
            json_encode([
                'nombre' => 'Test',
                'fecha_partida' => '2026-07-15',
                'precio' => 100,
                'hoteles_ids' => [],
            ]),
        );

        $response = $this->controller->crear($request);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_crear_sin_token_retorna_404(): void
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'nombre' => 'Test',
                'fecha_partida' => '2026-07-15',
                'precio' => 100,
                'hoteles_ids' => [1],
            ]),
        );

        $response = $this->controller->crear($request);

        $this->assertSame(404, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('Token de autenticación requerido.', $content['error']);
    }

    public function test_actualizar_retorna_200(): void
    {
        $this->paqueteService
            ->method('actualizar')
            ->willReturn(['id' => 1, 'nombre' => 'Paquete Actualizado']);

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
            json_encode([
                'nombre' => 'Paquete Actualizado',
                'fecha_partida' => '2026-08-01',
                'precio' => 2000.00,
                'hoteles_ids' => [1],
            ]),
        );

        $response = $this->controller->actualizar($request, ['id' => '1']);

        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('Paquete Actualizado', $content['nombre']);
    }

    public function test_actualizar_paquete_inexistente_retorna_404(): void
    {
        $this->paqueteService
            ->method('actualizar')
            ->willThrowException(new \RuntimeException('Paquete no encontrado.'));

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
            json_encode([
                'nombre' => 'Test',
                'fecha_partida' => '2026-08-01',
                'precio' => 100,
                'hoteles_ids' => [1],
            ]),
        );

        $response = $this->controller->actualizar($request, ['id' => '999']);

        $this->assertSame(404, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('Paquete no encontrado.', $content['error']);
    }

    public function test_actualizar_con_datos_invalidos_retorna_400(): void
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
            'invalid',
        );

        $response = $this->controller->actualizar($request, ['id' => '1']);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_eliminar_retorna_200(): void
    {
        $this->paqueteService
            ->expects($this->once())
            ->method('eliminar')
            ->with(1, 1);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->tokenValido],
        );

        $response = $this->controller->eliminar($request, ['id' => '1']);

        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('Paquete eliminado correctamente.', $content['mensaje']);
    }

    public function test_eliminar_paquete_inexistente_retorna_404(): void
    {
        $this->paqueteService
            ->method('eliminar')
            ->with(999, 1)
            ->willThrowException(new \RuntimeException('Paquete no encontrado.'));

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->tokenValido],
        );

        $response = $this->controller->eliminar($request, ['id' => '999']);

        $this->assertSame(404, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('Paquete no encontrado.', $content['error']);
    }
}
