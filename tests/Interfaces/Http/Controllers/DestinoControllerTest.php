<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Interfaces\Http\Controllers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use TiendaTurismo\GestionDatos\Application\Services\DestinoService;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\DestinoController;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\DestinoServiceMockTrait;

final class DestinoControllerTest extends TestCase
{
    use DestinoServiceMockTrait;

    private DestinoService $destinoService;
    private DestinoController $controller;

    protected function setUp(): void
    {
        $this->destinoService = $this->createDestinoServiceMock();
        $this->controller = new DestinoController($this->destinoService);
    }

    public function test_listar_retorna_destinos(): void
    {
        $this->destinoService
            ->method('listar')
            ->willReturn([['id' => 1, 'ciudad' => 'Buenos Aires']]);

        $request = new Request();
        $response = $this->controller->listar($request);

        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertSame('Buenos Aires', $content[0]['ciudad']);
    }

    public function test_crear_retorna_201(): void
    {
        $this->destinoService
            ->method('crear')
            ->willReturn(['id' => 1, 'ciudad' => 'Córdoba']);

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'ciudad' => 'Córdoba',
            'estado_provincia' => 'Córdoba',
            'pais' => 'Argentina',
        ]));

        $response = $this->controller->crear($request);

        $this->assertSame(201, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Córdoba', $content['ciudad']);
    }

    public function test_crear_con_datos_invalidos_retorna_400(): void
    {
        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], 'not json');

        $response = $this->controller->crear($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_actualizar_retorna_200(): void
    {
        $this->destinoService
            ->method('actualizar')
            ->willReturn(['id' => 1, 'ciudad' => 'Mendoza']);

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'ciudad' => 'Mendoza',
            'estado_provincia' => 'Mendoza',
            'pais' => 'Argentina',
        ]));

        $response = $this->controller->actualizar($request, ['id' => '1']);

        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Mendoza', $content['ciudad']);
    }

    public function test_actualizar_destino_inexistente_retorna_404(): void
    {
        $this->destinoService
            ->method('actualizar')
            ->willThrowException(new \RuntimeException('Destino no encontrado.'));

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'ciudad' => 'Mendoza',
            'estado_provincia' => 'Mendoza',
            'pais' => 'Argentina',
        ]));

        $response = $this->controller->actualizar($request, ['id' => '999']);

        $this->assertSame(404, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Destino no encontrado.', $content['error']);
    }

    public function test_actualizar_con_datos_invalidos_retorna_400(): void
    {
        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], 'invalid');

        $response = $this->controller->actualizar($request, ['id' => '1']);

        $this->assertSame(400, $response->getStatusCode());
    }
}
