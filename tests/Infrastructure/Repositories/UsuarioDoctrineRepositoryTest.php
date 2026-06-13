<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Infrastructure\Repositories;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Domain\Models\Usuario;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\UsuarioDoctrineRepository;
use TiendaTurismo\GestionDatos\Tests\Shared\EntityManagerMockFactory;

final class UsuarioDoctrineRepositoryTest extends TestCase
{
    use EntityManagerMockFactory;

    private UsuarioDoctrineRepository $repo;
    private Usuario $usuario;

    protected function setUp(): void
    {
        $this->crearMocksEntityManager($this, Usuario::class);

        $this->repo = new UsuarioDoctrineRepository($this->entityManager);

        $this->usuario = new Usuario(
            nombre: 'Juan',
            apellido: 'Pérez',
            email: 'juan@example.com',
            contrasena: 'hash',
            rol: 'admin',
        );
    }

    public function test_save_persists_and_flushes(): void
    {
        $this->entityManager->expects($this->once())->method('persist')->with($this->usuario);
        $this->entityManager->expects($this->once())->method('flush');

        $this->repo->save($this->usuario);
    }

    public function test_findById_delega_en_entityManager(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('find')
            ->with(Usuario::class, 5)
            ->willReturn($this->usuario);

        $resultado = $this->repo->findById(5);

        $this->assertSame($this->usuario, $resultado);
    }

    public function test_findById_retorna_null_si_no_existe(): void
    {
        $this->entityManager
            ->method('find')
            ->with(Usuario::class, 999)
            ->willReturn(null);

        $this->assertNull($this->repo->findById(999));
    }

    public function test_findByEmail_delega_en_repository(): void
    {
        $this->entityRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'juan@example.com'])
            ->willReturn($this->usuario);

        $resultado = $this->repo->findByEmail('juan@example.com');

        $this->assertSame($this->usuario, $resultado);
    }

    public function test_findByEmail_retorna_null_si_no_existe(): void
    {
        $this->entityRepository
            ->method('findOneBy')
            ->with(['email' => 'no@existe.com'])
            ->willReturn(null);

        $this->assertNull($this->repo->findByEmail('no@existe.com'));
    }

    public function test_save_hashea_contrasena_antes_de_persistir(): void
    {
        $usuario = new Usuario(
            nombre: 'Test',
            apellido: 'User',
            email: 'test@test.com',
            contrasena: 'mi_password_plano',
            rol: 'admin',
        );

        $this->entityManager->expects($this->once())->method('persist')->with($usuario);
        $this->entityManager->expects($this->once())->method('flush');

        $this->repo->save($usuario);

        $this->assertTrue(password_verify('mi_password_plano', $usuario->contrasena()));
        $this->assertStringStartsWith('$2y$', $usuario->contrasena());
    }

    public function test_findAll_retorna_lista_de_usuarios(): void
    {
        $usuarios = [$this->usuario];

        $this->entityRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($usuarios);

        $resultado = $this->repo->findAll();

        $this->assertSame($usuarios, $resultado);
    }

    public function test_update_lanza_excepcion_si_no_existe(): void
    {
        $noExiste = new Usuario(
            nombre: 'X',
            apellido: 'Y',
            email: 'x@y.com',
            contrasena: 'h',
            rol: 'lector',
            id: 999,
        );

        $this->entityManager
            ->method('find')
            ->with(Usuario::class, 999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Usuario con ID 999 no encontrado.');

        $this->repo->update($noExiste);
    }

    public function test_update_copia_propiedades_y_flushea(): void
    {
        $original = new Usuario(
            nombre: 'Original',
            apellido: 'Viejo',
            email: 'original@example.com',
            contrasena: 'old_hash',
            rol: 'lector',
            id: 1,
            fechaCreacion: new \DateTimeImmutable('2024-01-01'),
            fechaActualizacion: new \DateTimeImmutable('2024-01-01'),
        );

        $nuevosDatos = new Usuario(
            nombre: 'Actualizado',
            apellido: 'Nuevo',
            email: 'nuevo@example.com',
            contrasena: 'new_hash',
            rol: 'admin',
            id: 1,
        );

        $this->entityManager
            ->method('find')
            ->with(Usuario::class, 1)
            ->willReturn($original);

        $this->entityManager->expects($this->once())->method('flush');

        $this->repo->update($nuevosDatos);

        $this->assertSame('Actualizado', $original->nombre());
        $this->assertSame('Nuevo', $original->apellido());
        $this->assertSame('nuevo@example.com', $original->email());
        $this->assertTrue(password_verify('new_hash', $original->contrasena()));
        $this->assertSame('admin', $original->rol());
        $this->assertSame(1, $original->id());
        $this->assertEquals(new \DateTimeImmutable('2024-01-01'), $original->fechaCreacion());
    }

    public function test_update_actualiza_fecha_actualizacion(): void
    {
        $before = new \DateTimeImmutable('2020-01-01');
        $original = new Usuario(
            nombre: 'A',
            apellido: 'B',
            email: 'a@b.com',
            contrasena: 'h',
            rol: 'lector',
            id: 2,
            fechaCreacion: $before,
            fechaActualizacion: $before,
        );

        $nuevosDatos = new Usuario(
            nombre: 'A',
            apellido: 'B',
            email: 'a@b.com',
            contrasena: 'h',
            rol: 'lector',
            id: 2,
        );

        $this->entityManager
            ->method('find')
            ->with(Usuario::class, 2)
            ->willReturn($original);

        $this->repo->update($nuevosDatos);

        $this->assertNotEquals($before, $original->fechaActualizacion());
    }
}
