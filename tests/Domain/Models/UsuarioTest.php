<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Domain\Models;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Domain\Models\Usuario;

final class UsuarioTest extends TestCase
{
    private Usuario $usuario;

    protected function setUp(): void
    {
        $this->usuario = new Usuario(
            nombre: 'Juan',
            apellido: 'Pérez',
            numeroDocumento: 'DNI12345678',
            email: 'juan@example.com',
            contrasena: 'securePass123',
            rol: 'admin',
        );
    }

    public function test_constructor_asigna_valores_correctamente(): void
    {
        $this->assertSame('Juan', $this->usuario->nombre());
        $this->assertSame('Pérez', $this->usuario->apellido());
        $this->assertSame('DNI12345678', $this->usuario->numeroDocumento());
        $this->assertSame('juan@example.com', $this->usuario->email());
        $this->assertSame('securePass123', $this->usuario->contrasena());
        $this->assertSame('admin', $this->usuario->rol());
    }

    public function test_id_es_null_para_nuevo_usuario(): void
    {
        $this->assertNull($this->usuario->id());
    }

    public function test_fechas_son_generadas_automaticamente(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->usuario->fechaCreacion());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->usuario->fechaActualizacion());
    }

    public function test_id_puede_ser_asignado_externamente(): void
    {
        $usuario = new Usuario(
            nombre: 'Ana',
            apellido: 'García',
            numeroDocumento: 'DNI87654321',
            email: 'ana@example.com',
            contrasena: 'pass456',
            rol: 'editor',
            id: 5,
        );

        $this->assertSame(5, $usuario->id());
    }

    public function test_fechas_pueden_ser_inyectadas(): void
    {
        $creacion = new \DateTimeImmutable('2024-01-01 10:00:00');
        $actualizacion = new \DateTimeImmutable('2024-06-01 15:30:00');

        $usuario = new Usuario(
            nombre: 'Luis',
            apellido: 'Martínez',
            numeroDocumento: 'DNI11223344',
            email: 'luis@example.com',
            contrasena: 'pass789',
            rol: 'lector',
            id: 10,
            fechaCreacion: $creacion,
            fechaActualizacion: $actualizacion,
        );

        $this->assertSame($creacion, $usuario->fechaCreacion());
        $this->assertSame($actualizacion, $usuario->fechaActualizacion());
    }

    public function test_toArray_excluye_contrasena(): void
    {
        $arr = $this->usuario->toArray();

        $this->assertArrayHasKey('id', $arr);
        $this->assertArrayHasKey('nombre', $arr);
        $this->assertArrayHasKey('apellido', $arr);
        $this->assertArrayHasKey('numero_documento', $arr);
        $this->assertArrayHasKey('email', $arr);
        $this->assertArrayHasKey('rol', $arr);
        $this->assertArrayHasKey('fecha_creacion', $arr);
        $this->assertArrayHasKey('fecha_actualizacion', $arr);
        $this->assertArrayNotHasKey('contrasena', $arr);
    }

    public function test_toArray_retorna_valores_correctos(): void
    {
        $arr = $this->usuario->toArray();

        $this->assertNull($arr['id']);
        $this->assertSame('Juan', $arr['nombre']);
        $this->assertSame('Pérez', $arr['apellido']);
        $this->assertSame('DNI12345678', $arr['numero_documento']);
        $this->assertSame('juan@example.com', $arr['email']);
        $this->assertSame('admin', $arr['rol']);
    }

    public function test_constructor_lanza_excepcion_si_nombre_esta_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('nombre es obligatorio');

        new Usuario(
            nombre: '',
            apellido: 'Pérez',
            numeroDocumento: 'DNI12345678',
            email: 'juan@example.com',
            contrasena: 'pass',
            rol: 'admin',
        );
    }

    public function test_constructor_lanza_excepcion_si_apellido_esta_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('apellido es obligatorio');

        new Usuario(
            nombre: 'Juan',
            apellido: '',
            numeroDocumento: 'DNI12345678',
            email: 'juan@example.com',
            contrasena: 'pass',
            rol: 'admin',
        );
    }

    public function test_constructor_lanza_excepcion_si_numero_documento_esta_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('numero_documento es obligatorio');

        new Usuario(
            nombre: 'Juan',
            apellido: 'Pérez',
            numeroDocumento: '',
            email: 'juan@example.com',
            contrasena: 'pass',
            rol: 'admin',
        );
    }

    public function test_constructor_lanza_excepcion_si_contrasena_esta_vacia(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('contrasena es obligatorio');

        new Usuario(
            nombre: 'Juan',
            apellido: 'Pérez',
            numeroDocumento: 'DNI12345678',
            email: 'juan@example.com',
            contrasena: '',
            rol: 'admin',
        );
    }

    public function test_constructor_lanza_excepcion_si_rol_esta_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('rol es obligatorio');

        new Usuario(
            nombre: 'Juan',
            apellido: 'Pérez',
            numeroDocumento: 'DNI12345678',
            email: 'juan@example.com',
            contrasena: 'pass',
            rol: '',
        );
    }

    public function test_constructor_lanza_excepcion_si_email_esta_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('email es obligatorio');

        new Usuario(
            nombre: 'Juan',
            apellido: 'Pérez',
            numeroDocumento: 'DNI12345678',
            email: '',
            contrasena: 'pass',
            rol: 'admin',
        );
    }

    public function test_constructor_lanza_excepcion_si_email_es_invalido(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('email no tiene un formato válido');

        new Usuario(
            nombre: 'Juan',
            apellido: 'Pérez',
            numeroDocumento: 'DNI12345678',
            email: 'no-es-un-email',
            contrasena: 'pass',
            rol: 'admin',
        );
    }

    public function test_constructor_limpia_espacios_para_validacion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('numero_documento es obligatorio');

        new Usuario(
            nombre: 'Juan',
            apellido: 'Pérez',
            numeroDocumento: '   ',
            email: 'juan@example.com',
            contrasena: 'pass',
            rol: 'admin',
        );
    }

    public function test_general_con_id_completo(): void
    {
        $usuario = new Usuario(
            nombre: 'María',
            apellido: 'López',
            numeroDocumento: 'DNI99887766',
            email: 'maria@example.com',
            contrasena: 'hashed_password',
            rol: 'superadmin',
            id: 99,
            fechaCreacion: new \DateTimeImmutable('2025-01-01'),
            fechaActualizacion: new \DateTimeImmutable('2025-06-15'),
        );

        $this->assertSame(99, $usuario->id());
        $this->assertSame('María', $usuario->nombre());
        $this->assertSame('López', $usuario->apellido());
        $this->assertSame('DNI99887766', $usuario->numeroDocumento());
        $this->assertSame('maria@example.com', $usuario->email());
        $this->assertSame('hashed_password', $usuario->contrasena());
        $this->assertSame('superadmin', $usuario->rol());

        $arr = $usuario->toArray();
        $this->assertSame(99, $arr['id']);
        $this->assertSame('DNI99887766', $arr['numero_documento']);
        $this->assertSame('superadmin', $arr['rol']);
    }
}
