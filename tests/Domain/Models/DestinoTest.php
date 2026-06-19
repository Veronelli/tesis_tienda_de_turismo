<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Domain\Models;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Domain\Models\Destino;

final class DestinoTest extends TestCase
{
    private Destino $destino;

    protected function setUp(): void
    {
        $this->destino = new Destino(
            ciudad: 'Buenos Aires',
            estadoProvincia: 'CABA',
            pais: 'Argentina',
        );
    }

    public function test_constructor_asigna_valores_correctamente(): void
    {
        $this->assertSame('Buenos Aires', $this->destino->ciudad());
        $this->assertSame('CABA', $this->destino->estadoProvincia());
        $this->assertSame('Argentina', $this->destino->pais());
    }

    public function test_id_es_null_para_nuevo_destino(): void
    {
        $this->assertNull($this->destino->id());
    }

    public function test_fechas_son_generadas_automaticamente(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->destino->fechaCreacion());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->destino->fechaActualizacion());
    }

    public function test_id_puede_ser_asignado_externamente(): void
    {
        $destino = new Destino('Santiago', 'RM', 'Chile', id: 10);
        $this->assertSame(10, $destino->id());
    }

    public function test_fechas_pueden_ser_inyectadas(): void
    {
        $creacion = new \DateTimeImmutable('2024-01-01');
        $actualizacion = new \DateTimeImmutable('2024-06-01');

        $destino = new Destino(
            'Lima', 'Lima', 'Perú',
            id: 3,
            fechaCreacion: $creacion,
            fechaActualizacion: $actualizacion,
        );

        $this->assertSame($creacion, $destino->fechaCreacion());
        $this->assertSame($actualizacion, $destino->fechaActualizacion());
    }

    public function test_toArray_retorna_todos_los_campos(): void
    {
        $arr = $this->destino->toArray();

        $this->assertArrayHasKey('id', $arr);
        $this->assertArrayHasKey('ciudad', $arr);
        $this->assertArrayHasKey('estado_provincia', $arr);
        $this->assertArrayHasKey('pais', $arr);
        $this->assertArrayHasKey('fecha_creacion', $arr);
        $this->assertArrayHasKey('fecha_actualizacion', $arr);
    }

    public function test_toArray_retorna_valores_correctos(): void
    {
        $arr = $this->destino->toArray();

        $this->assertNull($arr['id']);
        $this->assertSame('Buenos Aires', $arr['ciudad']);
        $this->assertSame('CABA', $arr['estado_provincia']);
        $this->assertSame('Argentina', $arr['pais']);
    }

    public function test_constructor_lanza_excepcion_si_ciudad_esta_vacia(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ciudad es obligatorio');

        new Destino('', 'CABA', 'Argentina');
    }

    public function test_constructor_lanza_excepcion_si_estado_provincia_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('estado_provincia es obligatorio');

        new Destino('Buenos Aires', '', 'Argentina');
    }

    public function test_constructor_lanza_excepcion_si_pais_esta_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('pais es obligatorio');

        new Destino('Buenos Aires', 'CABA', '');
    }

    public function test_constructor_lanza_excepcion_si_ciudad_excede_150_caracteres(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ciudad no puede superar 150 caracteres');

        new Destino(str_repeat('a', 151), 'CABA', 'Argentina');
    }

    public function test_constructor_lanza_excepcion_si_estado_provincia_excede_150(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('estado_provincia no puede superar 150 caracteres');

        new Destino('Buenos Aires', str_repeat('a', 151), 'Argentina');
    }

    public function test_constructor_lanza_excepcion_si_pais_excede_150_caracteres(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('pais no puede superar 150 caracteres');

        new Destino('Buenos Aires', 'CABA', str_repeat('a', 151));
    }

    public function test_general_con_id_completo(): void
    {
        $destino = new Destino(
            'Montevideo', 'Montevideo', 'Uruguay',
            id: 99,
            fechaCreacion: new \DateTimeImmutable('2025-01-01'),
            fechaActualizacion: new \DateTimeImmutable('2025-06-15'),
        );

        $this->assertSame(99, $destino->id());
        $this->assertSame('Montevideo', $destino->ciudad());
        $this->assertSame('Montevideo', $destino->estadoProvincia());
        $this->assertSame('Uruguay', $destino->pais());

        $arr = $destino->toArray();
        $this->assertSame(99, $arr['id']);
        $this->assertSame('Uruguay', $arr['pais']);
    }
}
