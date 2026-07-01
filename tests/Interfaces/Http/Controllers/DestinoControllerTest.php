<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Interfaces\Http\Controllers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use TiendaTurismo\GestionDatos\Application\UseCases\Usuario\ObtenerUsuarioPorIdUseCase;
use TiendaTurismo\GestionDatos\Application\Services\DestinoService;
use TiendaTurismo\GestionDatos\Domain\Models\Destino;
use TiendaTurismo\GestionDatos\Domain\Models\Usuario;
use TiendaTurismo\GestionDatos\Domain\Repositories\DestinoRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\UsuarioRepositoryInterface;
use TiendaTurismo\GestionDatos\Infrastructure\Security\JwtService;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\DestinoController;

final class DestinoControllerTest extends TestCase
{
    private DestinoService $destinoService;
    private DestinoController $controller;
    private InMemoryDestinoRepository $repository;
    private InMemoryUsuarioRepository $usuarioRepository;

    protected function setUp(): void
    {
        $_ENV['JWT_SECRET'] = 'test_secret_key';
        $_ENV['JWT_TTL'] = '3600';

        $this->repository = new InMemoryDestinoRepository();
        $this->repository->seed(new Destino('Buenos Aires', 'Buenos Aires', 'Argentina', 1));

        $this->usuarioRepository = new InMemoryUsuarioRepository();
        $this->usuarioRepository->seed(new Usuario('Juan', 'Vendedor', 'vendedor@test.com', 'secret123', 'vendedor', 1));
        $this->usuarioRepository->seed(new Usuario('Ana', 'Lector', 'lector@test.com', 'secret123', 'lector', 2));

        $this->destinoService = new DestinoService($this->repository);
        $this->controller = new DestinoController(
            $this->destinoService,
            new ObtenerUsuarioPorIdUseCase($this->usuarioRepository),
        );
    }

    public function test_listar_retorna_destinos(): void
    {
        $request = new Request();
        $response = $this->controller->listar($request);

        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertSame('Buenos Aires', $content[0]['ciudad']);
    }

    public function test_crear_retorna_201(): void
    {
        $request = $this->crearRequestConToken([
            'ciudad' => 'Córdoba',
            'estado_provincia' => 'Córdoba',
            'pais' => 'Argentina',
        ]);

        $response = $this->controller->crear($request);

        $this->assertSame(201, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Córdoba', $content['ciudad']);
    }

    public function test_crear_como_lector_retorna_403(): void
    {
        $request = $this->crearRequestConToken([
            'ciudad' => 'Córdoba',
            'estado_provincia' => 'Córdoba',
            'pais' => 'Argentina',
        ], 'lector');

        $response = $this->controller->crear($request);

        $this->assertSame(403, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Los usuarios de tipo lector no pueden modificar destinos.', $content['error']);
    }

    public function test_crear_con_datos_invalidos_retorna_400(): void
    {
        $request = $this->crearRequestConToken('not json');

        $response = $this->controller->crear($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_actualizar_retorna_200(): void
    {
        $request = $this->crearRequestConToken([
            'ciudad' => 'Mendoza',
            'estado_provincia' => 'Mendoza',
            'pais' => 'Argentina',
        ]);

        $response = $this->controller->actualizar($request, ['id' => '1']);

        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Mendoza', $content['ciudad']);
    }

    public function test_actualizar_como_lector_retorna_403(): void
    {
        $request = $this->crearRequestConToken([
            'ciudad' => 'Mendoza',
            'estado_provincia' => 'Mendoza',
            'pais' => 'Argentina',
        ], 'lector');

        $response = $this->controller->actualizar($request, ['id' => '1']);

        $this->assertSame(403, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Los usuarios de tipo lector no pueden modificar destinos.', $content['error']);
    }

    public function test_actualizar_destino_inexistente_retorna_404(): void
    {
        $request = $this->crearRequestConToken([
            'ciudad' => 'Mendoza',
            'estado_provincia' => 'Mendoza',
            'pais' => 'Argentina',
        ]);

        $response = $this->controller->actualizar($request, ['id' => '999']);

        $this->assertSame(404, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Destino no encontrado.', $content['error']);
    }

    public function test_actualizar_con_datos_invalidos_retorna_400(): void
    {
        $request = $this->crearRequestConToken('invalid');

        $response = $this->controller->actualizar($request, ['id' => '1']);

        $this->assertSame(400, $response->getStatusCode());
    }

    /** @param array<string, mixed>|string $data */
    private function crearRequestConToken(array|string $data, string $rol = 'admin'): Request
    {
        $content = is_array($data) ? json_encode($data) : $data;
        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $content);
        $request->headers->set('Authorization', 'Bearer ' . $this->crearToken($rol));

        return $request;
    }

    private function crearToken(string $rol): string
    {
        return (new JwtService())->encode([
            'sub' => $rol === 'lector' ? 2 : 1,
            'email' => $rol === 'lector' ? 'lector@test.com' : 'vendedor@test.com',
            'rol' => $rol,
        ]);
    }
}

final class InMemoryDestinoRepository implements DestinoRepositoryInterface
{
    /** @var array<int, Destino> */
    private array $destinos = [];

    public function seed(Destino $destino): void
    {
        $this->destinos[$destino->id() ?? count($this->destinos) + 1] = $destino;
    }

    public function save(Destino $destino): void
    {
        $this->destinos[$destino->id() ?? count($this->destinos) + 1] = $destino;
    }

    public function findById(int $id): ?Destino
    {
        return $this->destinos[$id] ?? null;
    }

    public function update(Destino $destino): void
    {
        if ($destino->id() === null) {
            return;
        }

        $this->destinos[$destino->id()] = $destino;
    }

    public function findAll(): array
    {
        return array_values($this->destinos);
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
