<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Domain\Models;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Domain\Models\Cliente;

final class ClienteTest extends TestCase
{
    private Cliente $cliente;

    protected function setUp(): void
    {
        $this->cliente = new Cliente(
            'Juan',
            'Pérez',
            'juan@example.com',
            '123456789',
            '12345678',
            'Buenos Aires',
        );
    }

    public function test_constructor_asigna_valores_correctamente(): void
    {
        $this->assertSame('Juan', $this->cliente->nombre());
        $this->assertSame('Pérez', $this->cliente->apellido());
        $this->assertSame('juan@example.com', $this->cliente->email());
        $this->assertSame('123456789', $this->cliente->telefono());
        $this->assertSame('12345678', $this->cliente->dni());
        $this->assertSame('Buenos Aires', $this->cliente->ubicacion());
    }

    public function test_id_es_null_para_nuevo_cliente(): void
    {
        $this->assertNull($this->cliente->id());
    }

    public function test_fechas_son_generadas_automaticamente(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->cliente->fechaCreacion());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->cliente->fechaActualizacion());
    }

    public function test_id_puede_ser_asignado_externamente(): void
    {
        $cliente = new Cliente('Ana', 'López', 'ana@example.com', '111', '222', 'CABA', id: 10);
        $this->assertSame(10, $cliente->id());
    }

    public function test_fechas_pueden_ser_inyectadas(): void
    {
        $creacion = new \DateTimeImmutable('2024-01-01');
        $actualizacion = new \DateTimeImmutable('2024-06-01');

        $cliente = new Cliente(
            'Ana', 'López', 'ana@example.com', '111', '222', 'CABA',
            id: 3,
            fechaCreacion: $creacion,
            fechaActualizacion: $actualizacion,
        );

        $this->assertSame($creacion, $cliente->fechaCreacion());
        $this->assertSame($actualizacion, $cliente->fechaActualizacion());
    }

    public function test_toArray_retorna_todos_los_campos(): void
    {
        $arr = $this->cliente->toArray();

        $this->assertArrayHasKey('id', $arr);
        $this->assertArrayHasKey('nombre', $arr);
        $this->assertArrayHasKey('apellido', $arr);
        $this->assertArrayHasKey('email', $arr);
        $this->assertArrayHasKey('telefono', $arr);
        $this->assertArrayHasKey('dni', $arr);
        $this->assertArrayHasKey('ubicacion', $arr);
        $this->assertArrayHasKey('fecha_creacion', $arr);
        $this->assertArrayHasKey('fecha_actualizacion', $arr);
    }

    public function test_toArray_retorna_valores_correctos(): void
    {
        $arr = $this->cliente->toArray();

        $this->assertNull($arr['id']);
        $this->assertSame('Juan', $arr['nombre']);
        $this->assertSame('Pérez', $arr['apellido']);
        $this->assertSame('juan@example.com', $arr['email']);
        $this->assertSame('123456789', $arr['telefono']);
        $this->assertSame('12345678', $arr['dni']);
        $this->assertSame('Buenos Aires', $arr['ubicacion']);
    }

    public function test_constructor_lanza_excepcion_si_nombre_esta_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('nombre es obligatorio');

        new Cliente('', 'Pérez', 'juan@example.com', '123456789', '12345678', 'Buenos Aires');
    }

    public function test_constructor_lanza_excepcion_si_apellido_esta_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('apellido es obligatorio');

        new Cliente('Juan', '', 'juan@example.com', '123456789', '12345678', 'Buenos Aires');
    }

    public function test_constructor_lanza_excepcion_si_email_esta_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('email es obligatorio');

        new Cliente('Juan', 'Pérez', '', '123456789', '12345678', 'Buenos Aires');
    }

    public function test_constructor_lanza_excepcion_si_email_invalido(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('email no es válido');

        new Cliente('Juan', 'Pérez', 'invalido', '123456789', '12345678', 'Buenos Aires');
    }

    public function test_constructor_lanza_excepcion_si_telefono_esta_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('telefono es obligatorio');

        new Cliente('Juan', 'Pérez', 'juan@example.com', '', '12345678', 'Buenos Aires');
    }

    public function test_constructor_lanza_excepcion_si_dni_esta_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('dni es obligatorio');

        new Cliente('Juan', 'Pérez', 'juan@example.com', '123456789', '', 'Buenos Aires');
    }

    public function test_constructor_lanza_excepcion_si_ubicacion_esta_vacia(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ubicacion es obligatorio');

        new Cliente('Juan', 'Pérez', 'juan@example.com', '123456789', '12345678', '');
    }

    public function test_update_modifica_valores_correctamente(): void
    {
        $this->cliente->update('Ana', 'López', 'ana@example.com', '999', '888', 'Córdoba');

        $this->assertSame('Ana', $this->cliente->nombre());
        $this->assertSame('López', $this->cliente->apellido());
        $this->assertSame('ana@example.com', $this->cliente->email());
        $this->assertSame('999', $this->cliente->telefono());
        $this->assertSame('888', $this->cliente->dni());
        $this->assertSame('Córdoba', $this->cliente->ubicacion());
    }

    public function test_general_con_id_completo(): void
    {
        $cliente = new Cliente(
            'Carlos',
            'García',
            'carlos@example.com',
            '555',
            '11111111',
            'Mendoza',
            id: 99,
            fechaCreacion: new \DateTimeImmutable('2025-01-01'),
            fechaActualizacion: new \DateTimeImmutable('2025-06-15'),
        );

        $this->assertSame(99, $cliente->id());
        $this->assertSame('Carlos', $cliente->nombre());
        $this->assertSame('García', $cliente->apellido());
        $this->assertSame('carlos@example.com', $cliente->email());

        $arr = $cliente->toArray();
        $this->assertSame(99, $arr['id']);
        $this->assertSame('Mendoza', $arr['ubicacion']);
    }
}
