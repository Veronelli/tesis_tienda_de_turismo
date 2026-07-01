<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Interfaces\Http\Controllers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use TiendaTurismo\GestionDatos\Application\Services\ClienteService;
use TiendaTurismo\GestionDatos\Application\UseCases\Usuario\ObtenerUsuarioPorIdUseCase;
use TiendaTurismo\GestionDatos\Domain\Models\Usuario;
use TiendaTurismo\GestionDatos\Domain\Repositories\UsuarioRepositoryInterface;
use TiendaTurismo\GestionDatos\Infrastructure\Security\JwtService;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\ClienteController;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\ClienteServiceMockTrait;

final class ClienteControllerTest extends TestCase
{
    use ClienteServiceMockTrait;

    private ClienteService $clienteService;
    private ClienteController $controller;
    private string $tokenVendedor;
    private string $tokenLector;

    protected function setUp(): void
    {
        $_ENV['JWT_SECRET'] = 'test_secret_key';
        $_ENV['JWT_TTL'] = '3600';

        $usuarioRepository = new InMemoryUsuarioRepository();
        $usuarioRepository->seed(new Usuario('Juan', 'Vendedor', 'vendedor@test.com', 'secret123', 'vendedor', 1));
        $usuarioRepository->seed(new Usuario('Ana', 'Lector', 'lector@test.com', 'secret123', 'lector', 2));

        $jwt = new JwtService();
        $this->tokenVendedor = $jwt->encode(['sub' => 1, 'email' => 'vendedor@test.com', 'rol' => 'vendedor']);
        $this->tokenLector = $jwt->encode(['sub' => 2, 'email' => 'lector@test.com', 'rol' => 'lector']);

        $this->clienteService = $this->createClienteServiceMock();
        $this->controller = new ClienteController(
            $this->clienteService,
            new ObtenerUsuarioPorIdUseCase($usuarioRepository),
        );
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

        $request = $this->crearRequestConToken([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'telefono' => '123456789',
            'dni' => '12345678',
            'ubicacion' => 'Buenos Aires',
        ]);

        $response = $this->controller->crear($request);

        $this->assertSame(201, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Juan', $content['nombre']);
    }

    public function test_crear_como_lector_retorna_403(): void
    {
        $request = $this->crearRequestConToken([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'telefono' => '123456789',
            'dni' => '12345678',
            'ubicacion' => 'Buenos Aires',
        ], false);

        $response = $this->controller->crear($request);

        $this->assertSame(403, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Los usuarios de tipo lector no pueden modificar clientes.', $content['error']);
    }

    public function test_crear_con_datos_invalidos_retorna_400(): void
    {
        $request = $this->crearRequestConToken('invalid json');

        $response = $this->controller->crear($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_crear_con_servicio_null_retorna_404(): void
    {
        $this->clienteService
            ->method('crear')
            ->willThrowException(new \RuntimeException('Cliente no encontrado.'));

        $request = $this->crearRequestConToken([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'telefono' => '123456789',
            'dni' => '12345678',
            'ubicacion' => 'Buenos Aires',
        ]);

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

        $request = $this->crearRequestConToken([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'telefono' => '123456789',
            'dni' => '12345678',
            'ubicacion' => 'Buenos Aires',
        ]);

        $response = $this->controller->crear($request);

        $this->assertSame(409, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Ya existe un cliente con ese email.', $content['error']);
    }

    public function test_actualizar_como_lector_retorna_403(): void
    {
        $request = $this->crearRequestConToken([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'duplicado@example.com',
            'telefono' => '123456789',
            'dni' => '12345678',
            'ubicacion' => 'Buenos Aires',
        ], false);

        $response = $this->controller->actualizar($request, ['id' => '2']);

        $this->assertSame(403, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Los usuarios de tipo lector no pueden modificar clientes.', $content['error']);
    }

    public function test_actualizar_con_email_duplicado_retorna_409(): void
    {
        $this->clienteService
            ->method('actualizar')
            ->willThrowException(new \TiendaTurismo\GestionDatos\Domain\Exceptions\DuplicadoException('Ya existe un cliente con ese email.'));

        $request = $this->crearRequestConToken([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'duplicado@example.com',
            'telefono' => '123456789',
            'dni' => '12345678',
            'ubicacion' => 'Buenos Aires',
        ]);

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

        $request = $this->crearRequestConToken([
            'nombre' => 'Ana',
            'apellido' => 'López',
            'email' => 'ana@example.com',
            'telefono' => '999',
            'dni' => '888',
            'ubicacion' => 'Córdoba',
        ]);

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

        $request = $this->crearRequestConToken([
            'nombre' => 'Ana',
            'apellido' => 'López',
            'email' => 'ana@example.com',
            'telefono' => '999',
            'dni' => '888',
            'ubicacion' => 'Córdoba',
        ]);

        $response = $this->controller->actualizar($request, ['id' => '999']);

        $this->assertSame(404, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Cliente no encontrado.', $content['error']);
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
        $token = $vendedor ? $this->tokenVendedor : $this->tokenLector;
        $content = is_array($data) ? json_encode($data) : $data;
        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $content);
        $request->headers->set('Authorization', 'Bearer ' . $token);

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
