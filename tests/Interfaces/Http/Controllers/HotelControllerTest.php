<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Interfaces\Http\Controllers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use TiendaTurismo\GestionDatos\Application\UseCases\Usuario\ObtenerUsuarioPorIdUseCase;
use TiendaTurismo\GestionDatos\Application\Services\HotelService;
use TiendaTurismo\GestionDatos\Domain\Models\Usuario;
use TiendaTurismo\GestionDatos\Domain\Repositories\UsuarioRepositoryInterface;
use TiendaTurismo\GestionDatos\Infrastructure\Security\JwtService;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\HotelController;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\HotelServiceMockTrait;

final class HotelControllerTest extends TestCase
{
    use HotelServiceMockTrait;

    private HotelService $hotelService;
    private HotelController $controller;
    private InMemoryUsuarioRepository $usuarioRepository;
    private string $tokenVendedor;
    private string $tokenLector;

    protected function setUp(): void
    {
        $_ENV['JWT_SECRET'] = 'test_secret_key';
        $_ENV['JWT_TTL'] = '3600';

        $this->hotelService = $this->createHotelServiceMock();
        $this->usuarioRepository = new InMemoryUsuarioRepository();
        $this->usuarioRepository->seed(new Usuario('Juan', 'Vendedor', 'vendedor@test.com', 'secret123', 'vendedor', 1));
        $this->usuarioRepository->seed(new Usuario('Ana', 'Lector', 'lector@test.com', 'secret123', 'lector', 2));

        $jwt = new JwtService();
        $this->tokenVendedor = $jwt->encode(['sub' => 1, 'email' => 'vendedor@test.com', 'rol' => 'vendedor']);
        $this->tokenLector = $jwt->encode(['sub' => 2, 'email' => 'lector@test.com', 'rol' => 'lector']);

        $this->controller = new HotelController(
            $this->hotelService,
            new ObtenerUsuarioPorIdUseCase($this->usuarioRepository),
        );
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

        $request = $this->crearRequestConToken([
            'nombre' => 'Hotel Nuevo',
            'ubicacion' => 'Calle 123',
            'destino_id' => 1,
        ]);

        $response = $this->controller->crear($request);

        $this->assertSame(201, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Hotel Nuevo', $content['nombre']);
    }

    public function test_crear_con_datos_invalidos_retorna_400(): void
    {
        $request = $this->crearRequestConToken('invalid json');

        $response = $this->controller->crear($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_crear_como_lector_retorna_403(): void
    {
        $request = $this->crearRequestConToken([
            'nombre' => 'Hotel Nuevo',
            'ubicacion' => 'Calle 123',
            'destino_id' => 1,
        ], false);

        $response = $this->controller->crear($request);

        $this->assertSame(403, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Los usuarios de tipo lector no pueden modificar destinos.', $content['error']);
    }

    public function test_crear_con_destino_inexistente_retorna_404(): void
    {
        $this->hotelService
            ->method('crear')
            ->willThrowException(new \RuntimeException('Destino no encontrado.'));

        $request = $this->crearRequestConToken([
            'nombre' => 'Hotel',
            'ubicacion' => 'Calle',
            'destino_id' => 999,
        ]);

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

        $request = $this->crearRequestConToken([
            'nombre' => 'Hotel Actualizado',
            'ubicacion' => 'Calle 456',
            'destino_id' => 1,
        ]);

        $response = $this->controller->actualizar($request, ['id' => '1']);

        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Hotel Actualizado', $content['nombre']);
    }

    public function test_actualizar_como_lector_retorna_403(): void
    {
        $request = $this->crearRequestConToken([
            'nombre' => 'Hotel Actualizado',
            'ubicacion' => 'Calle 456',
            'destino_id' => 1,
        ], false);

        $response = $this->controller->actualizar($request, ['id' => '1']);

        $this->assertSame(403, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Los usuarios de tipo lector no pueden modificar destinos.', $content['error']);
    }

    public function test_actualizar_hotel_inexistente_retorna_404(): void
    {
        $this->hotelService
            ->method('actualizar')
            ->willThrowException(new \RuntimeException('Hotel no encontrado.'));

        $request = $this->crearRequestConToken([
            'nombre' => 'Hotel',
            'ubicacion' => 'Calle',
            'destino_id' => 1,
        ]);

        $response = $this->controller->actualizar($request, ['id' => '999']);

        $this->assertSame(404, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Hotel no encontrado.', $content['error']);
    }

    public function test_actualizar_con_datos_invalidos_retorna_400(): void
    {
        $request = $this->crearRequestConToken('invalid');

        $response = $this->controller->actualizar($request, ['id' => '1']);

        $this->assertSame(400, $response->getStatusCode());
    }

    /** @param array<string, mixed>|string $data */
    private function crearRequestConToken(array|string $data, bool $vendedor = true): Request
    {
        $content = is_array($data) ? json_encode($data) : $data;
        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $content);
        $request->headers->set('Authorization', 'Bearer ' . ($vendedor ? $this->tokenVendedor : $this->tokenLector));

        return $request;
    }
}

final class InMemoryUsuarioRepository implements UsuarioRepositoryInterface
{
    /** @var array<int, Usuario> */
    private array $usuarios = [];

    public function seed(Usuario $usuario): void
    {
        if ($usuario->id() === null) {
            return;
        }

        $this->usuarios[$usuario->id()] = $usuario;
    }

    public function save(Usuario $usuario): void
    {
        $this->seed($usuario);
    }

    public function update(Usuario $usuario): void
    {
        $this->seed($usuario);
    }

    public function findById(int $id): ?Usuario
    {
        return $this->usuarios[$id] ?? null;
    }

    public function findByEmail(string $email): ?Usuario
    {
        foreach ($this->usuarios as $usuario) {
            if ($usuario->email() === $email) {
                return $usuario;
            }
        }

        return null;
    }

    public function findAll(): array
    {
        return array_values($this->usuarios);
    }
}
