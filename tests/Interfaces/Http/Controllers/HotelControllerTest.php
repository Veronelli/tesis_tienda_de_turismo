<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Interfaces\Http\Controllers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use TiendaTurismo\GestionDatos\Application\Services\HotelService;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\HotelController;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\HotelServiceMockTrait;

final class HotelControllerTest extends TestCase
{
    use HotelServiceMockTrait;

    private HotelService $hotelService;
    private HotelController $controller;

    protected function setUp(): void
    {
        $this->hotelService = $this->createHotelServiceMock();
        $this->controller = new HotelController($this->hotelService);
    }

    public function test_listar_retorna_hoteles(): void
    {
        $this->hotelService
            ->method('listar')
            ->willReturn([['id' => 1, 'nombre' => 'Hotel A']]);

        $request = new Request();
        $response = $this->controller->listar($request);

        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertSame('Hotel A', $content[0]['nombre']);
    }

    public function test_crear_retorna_201(): void
    {
        $this->hotelService
            ->method('crear')
            ->willReturn(['id' => 1, 'nombre' => 'Hotel Nuevo']);

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nombre' => 'Hotel Nuevo',
            'ubicacion' => 'Calle 123',
            'destino_id' => 1,
        ]));

        $response = $this->controller->crear($request);

        $this->assertSame(201, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Hotel Nuevo', $content['nombre']);
    }

    public function test_crear_con_datos_invalidos_retorna_400(): void
    {
        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], 'invalid json');

        $response = $this->controller->crear($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_crear_con_destino_inexistente_retorna_404(): void
    {
        $this->hotelService
            ->method('crear')
            ->willThrowException(new \RuntimeException('Destino no encontrado.'));

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nombre' => 'Hotel',
            'ubicacion' => 'Calle',
            'destino_id' => 999,
        ]));

        $response = $this->controller->crear($request);

        $this->assertSame(404, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Destino no encontrado.', $content['error']);
    }

    public function test_actualizar_retorna_200(): void
    {
        $this->hotelService
            ->method('actualizar')
            ->willReturn(['id' => 1, 'nombre' => 'Hotel Actualizado']);

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nombre' => 'Hotel Actualizado',
            'ubicacion' => 'Calle 456',
            'destino_id' => 1,
        ]));

        $response = $this->controller->actualizar($request, ['id' => '1']);

        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Hotel Actualizado', $content['nombre']);
    }

    public function test_actualizar_hotel_inexistente_retorna_404(): void
    {
        $this->hotelService
            ->method('actualizar')
            ->willThrowException(new \RuntimeException('Hotel no encontrado.'));

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nombre' => 'Hotel',
            'ubicacion' => 'Calle',
            'destino_id' => 1,
        ]));

        $response = $this->controller->actualizar($request, ['id' => '999']);

        $this->assertSame(404, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Hotel no encontrado.', $content['error']);
    }

    public function test_actualizar_con_datos_invalidos_retorna_400(): void
    {
        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], 'invalid');

        $response = $this->controller->actualizar($request, ['id' => '1']);

        $this->assertSame(400, $response->getStatusCode());
    }
}
