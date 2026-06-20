<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Domain\Models;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Domain\Models\Destino;
use TiendaTurismo\GestionDatos\Domain\Models\Hotel;

final class HotelTest extends TestCase
{
    private Destino $destino;
    private Hotel $hotel;

    protected function setUp(): void
    {
        $this->destino = new Destino('Buenos Aires', 'CABA', 'Argentina', id: 1);
        $this->hotel = new Hotel('Sheraton', 'Av. Corrientes 1234', $this->destino);
    }

    public function test_constructor_asigna_valores_correctamente(): void
    {
        $this->assertSame('Sheraton', $this->hotel->nombre());
        $this->assertSame('Av. Corrientes 1234', $this->hotel->ubicacion());
        $this->assertSame($this->destino, $this->hotel->destino());
    }

    public function test_id_es_null_para_nuevo_hotel(): void
    {
        $this->assertNull($this->hotel->id());
    }

    public function test_fechas_son_generadas_automaticamente(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->hotel->fechaCreacion());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->hotel->fechaActualizacion());
    }

    public function test_id_puede_ser_asignado_externamente(): void
    {
        $hotel = new Hotel('Hotel Panamericano', 'Av. 9 de Julio 123', $this->destino, id: 10);
        $this->assertSame(10, $hotel->id());
    }

    public function test_fechas_pueden_ser_inyectadas(): void
    {
        $creacion = new \DateTimeImmutable('2024-01-01');
        $actualizacion = new \DateTimeImmutable('2024-06-01');

        $hotel = new Hotel(
            'Hotel Panamericano', 'Av. 9 de Julio 123', $this->destino,
            id: 3,
            fechaCreacion: $creacion,
            fechaActualizacion: $actualizacion,
        );

        $this->assertSame($creacion, $hotel->fechaCreacion());
        $this->assertSame($actualizacion, $hotel->fechaActualizacion());
    }

    public function test_toArray_retorna_todos_los_campos(): void
    {
        $arr = $this->hotel->toArray();

        $this->assertArrayHasKey('id', $arr);
        $this->assertArrayHasKey('nombre', $arr);
        $this->assertArrayHasKey('ubicacion', $arr);
        $this->assertArrayHasKey('destino_id', $arr);
        $this->assertArrayHasKey('destino', $arr);
        $this->assertArrayHasKey('fecha_creacion', $arr);
        $this->assertArrayHasKey('fecha_actualizacion', $arr);
    }

    public function test_toArray_retorna_valores_correctos(): void
    {
        $arr = $this->hotel->toArray();

        $this->assertNull($arr['id']);
        $this->assertSame('Sheraton', $arr['nombre']);
        $this->assertSame('Av. Corrientes 1234', $arr['ubicacion']);
        $this->assertSame(1, $arr['destino_id']);
    }

    public function test_constructor_lanza_excepcion_si_nombre_esta_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('nombre es obligatorio');

        new Hotel('', 'Av. Corrientes 1234', $this->destino);
    }

    public function test_constructor_lanza_excepcion_si_ubicacion_esta_vacia(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ubicacion es obligatorio');

        new Hotel('Sheraton', '', $this->destino);
    }

    public function test_constructor_lanza_excepcion_si_nombre_excede_200_caracteres(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('nombre no puede superar 200 caracteres');

        new Hotel(str_repeat('a', 201), 'Av. Corrientes 1234', $this->destino);
    }

    public function test_constructor_lanza_excepcion_si_ubicacion_excede_255_caracteres(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ubicacion no puede superar 255 caracteres');

        new Hotel('Sheraton', str_repeat('a', 256), $this->destino);
    }

    public function test_update_modifica_valores_correctamente(): void
    {
        $nuevoDestino = new Destino('Córdoba', 'Córdoba', 'Argentina', id: 2);
        $this->hotel->update('Hotel Cordoba', 'Av. Colon 500', $nuevoDestino);

        $this->assertSame('Hotel Cordoba', $this->hotel->nombre());
        $this->assertSame('Av. Colon 500', $this->hotel->ubicacion());
        $this->assertSame($nuevoDestino, $this->hotel->destino());
    }

    public function test_general_con_id_completo(): void
    {
        $destino = new Destino('Montevideo', 'Montevideo', 'Uruguay', id: 5);
        $hotel = new Hotel(
            'Hotel Montevideo',
            'Rambla 123',
            $destino,
            id: 99,
            fechaCreacion: new \DateTimeImmutable('2025-01-01'),
            fechaActualizacion: new \DateTimeImmutable('2025-06-15'),
        );

        $this->assertSame(99, $hotel->id());
        $this->assertSame('Hotel Montevideo', $hotel->nombre());
        $this->assertSame('Rambla 123', $hotel->ubicacion());
        $this->assertSame($destino, $hotel->destino());

        $arr = $hotel->toArray();
        $this->assertSame(99, $arr['id']);
        $this->assertSame(5, $arr['destino_id']);
    }
}
