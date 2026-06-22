<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Domain\Models;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Domain\Models\Destino;
use TiendaTurismo\GestionDatos\Domain\Models\Hotel;
use TiendaTurismo\GestionDatos\Domain\Models\Paquete;
use TiendaTurismo\GestionDatos\Domain\Models\Usuario;

final class PaqueteTest extends TestCase
{
    private Usuario $usuario;
    private Destino $destino;
    private Hotel $hotel;
    private Paquete $paquete;

    protected function setUp(): void
    {
        $this->usuario = new Usuario(
            nombre: 'Admin',
            apellido: 'Test',
            email: 'admin@test.com',
            contrasena: 'hashed',
            rol: 'admin',
            id: 1,
        );

        $this->destino = new Destino(
            ciudad: 'Buenos Aires',
            estadoProvincia: 'CABA',
            pais: 'Argentina',
            id: 1,
        );

        $this->hotel = new Hotel(
            nombre: 'Hotel Test',
            ubicacion: 'Centro',
            destino: $this->destino,
            id: 1,
        );

        $this->paquete = new Paquete(
            nombre: 'Paquete Test',
            descripcion: 'Descripción test',
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: new \DateTimeImmutable('2026-07-22'),
            precio: '1500.00',
            disponible: true,
            creadoPor: $this->usuario,
        );
    }

    public function test_constructor_asigna_valores_correctamente(): void
    {
        $this->assertSame('Paquete Test', $this->paquete->nombre());
        $this->assertSame('Descripción test', $this->paquete->descripcion());
        $this->assertEquals(new \DateTimeImmutable('2026-07-15'), $this->paquete->fechaPartida());
        $this->assertEquals(new \DateTimeImmutable('2026-07-22'), $this->paquete->fechaVuelta());
        $this->assertSame('1500.00', $this->paquete->precio());
        $this->assertTrue($this->paquete->disponible());
        $this->assertSame($this->usuario, $this->paquete->creadoPor());
        $this->assertNull($this->paquete->actualizadoPor());
    }

    public function test_id_es_null_para_nuevo_paquete(): void
    {
        $this->assertNull($this->paquete->id());
    }

    public function test_hoteles_coleccion_vacia_por_defecto(): void
    {
        $this->assertCount(0, $this->paquete->hoteles());
    }

    public function test_syncHoteles_agrega_hoteles(): void
    {
        $this->paquete->syncHoteles([$this->hotel]);

        $this->assertCount(1, $this->paquete->hoteles());
        $this->assertSame($this->hotel, $this->paquete->hoteles()->first());
    }

    public function test_syncHoteles_reemplaza_hoteles_existentes(): void
    {
        $destino2 = new Destino('Córdoba', 'Córdoba', 'Argentina', id: 2);
        $hotel2 = new Hotel('Hotel 2', 'Norte', $destino2, id: 2);

        $this->paquete->syncHoteles([$this->hotel]);
        $this->assertCount(1, $this->paquete->hoteles());

        $this->paquete->syncHoteles([$hotel2]);
        $this->assertCount(1, $this->paquete->hoteles());
        $this->assertSame($hotel2, $this->paquete->hoteles()->first());
    }

    public function test_update_modifica_datos_y_registra_actualizador(): void
    {
        $otroUsuario = new Usuario('Otro', 'User', 'otro@test.com', 'hash', 'admin', id: 2);

        $this->paquete->update(
            nombre: 'Paquete Modificado',
            descripcion: 'Nueva descripción',
            fechaPartida: new \DateTimeImmutable('2026-08-01'),
            fechaVuelta: new \DateTimeImmutable('2026-08-10'),
            precio: '2000.00',
            disponible: false,
            actualizadoPor: $otroUsuario,
        );

        $this->assertSame('Paquete Modificado', $this->paquete->nombre());
        $this->assertSame('Nueva descripción', $this->paquete->descripcion());
        $this->assertEquals(new \DateTimeImmutable('2026-08-01'), $this->paquete->fechaPartida());
        $this->assertEquals(new \DateTimeImmutable('2026-08-10'), $this->paquete->fechaVuelta());
        $this->assertSame('2000.00', $this->paquete->precio());
        $this->assertFalse($this->paquete->disponible());
        $this->assertSame($otroUsuario, $this->paquete->actualizadoPor());
    }

    public function test_toArray_retorna_todos_los_campos(): void
    {
        $this->paquete->syncHoteles([$this->hotel]);
        $arr = $this->paquete->toArray();

        $this->assertArrayHasKey('id', $arr);
        $this->assertArrayHasKey('nombre', $arr);
        $this->assertArrayHasKey('descripcion', $arr);
        $this->assertArrayHasKey('fecha_partida', $arr);
        $this->assertArrayHasKey('fecha_vuelta', $arr);
        $this->assertArrayHasKey('imagen_secundaria', $arr);
        $this->assertArrayHasKey('precio', $arr);
        $this->assertArrayHasKey('disponible', $arr);
        $this->assertArrayHasKey('creado_por', $arr);
        $this->assertArrayHasKey('actualizado_por', $arr);
        $this->assertArrayHasKey('destinos', $arr);
        $this->assertArrayHasKey('hoteles', $arr);
        $this->assertArrayHasKey('fecha_creacion', $arr);
        $this->assertArrayHasKey('fecha_actualizacion', $arr);
    }

    public function test_toArray_retorna_valores_correctos(): void
    {
        $this->paquete->syncHoteles([$this->hotel]);
        $arr = $this->paquete->toArray();

        $this->assertNull($arr['id']);
        $this->assertSame('Paquete Test', $arr['nombre']);
        $this->assertSame('Descripción test', $arr['descripcion']);
        $this->assertSame('2026-07-15', $arr['fecha_partida']);
        $this->assertSame('2026-07-22', $arr['fecha_vuelta']);
        $this->assertSame('1500.00', $arr['precio']);
        $this->assertTrue($arr['disponible']);
        $this->assertCount(1, $arr['hoteles']);
        $this->assertCount(1, $arr['destinos']);
    }

    public function test_constructor_con_imagen_principal(): void
    {
        $paquete = new Paquete(
            nombre: 'Paquete con Imagen',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: null,
            precio: '100',
            disponible: true,
            creadoPor: $this->usuario,
            imagenPrincipal: '/uploads/paquetes/paq_test.jpg',
        );

        $this->assertSame('/uploads/paquetes/paq_test.jpg', $paquete->imagenPrincipal());
    }

    public function test_constructor_sin_imagen_principal_por_defecto(): void
    {
        $this->assertNull($this->paquete->imagenPrincipal());
    }

    public function test_update_actualiza_imagen_principal(): void
    {
        $this->paquete->update(
            nombre: 'Paquete Modificado',
            descripcion: 'Nueva descripción',
            fechaPartida: new \DateTimeImmutable('2026-08-01'),
            fechaVuelta: new \DateTimeImmutable('2026-08-10'),
            precio: '2000.00',
            disponible: false,
            actualizadoPor: $this->usuario,
            imagenPrincipal: '/uploads/paquetes/nueva.jpg',
        );

        $this->assertSame('/uploads/paquetes/nueva.jpg', $this->paquete->imagenPrincipal());
    }

    public function test_update_puede_poner_imagen_principal_null(): void
    {
        $paquete = new Paquete(
            nombre: 'Test',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: null,
            precio: '100',
            disponible: true,
            creadoPor: $this->usuario,
            imagenPrincipal: '/uploads/paquetes/vieja.jpg',
        );

        $paquete->update(
            nombre: 'Test',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: null,
            precio: '100',
            disponible: true,
            actualizadoPor: $this->usuario,
            imagenPrincipal: null,
        );

        $this->assertNull($paquete->imagenPrincipal());
    }

    public function test_toArray_incluye_imagen_principal(): void
    {
        $paquete = new Paquete(
            nombre: 'Test',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: null,
            precio: '100',
            disponible: true,
            creadoPor: $this->usuario,
            imagenPrincipal: '/uploads/paquetes/test.jpg',
        );

        $arr = $paquete->toArray();

        $this->assertArrayHasKey('imagen_principal', $arr);
        $this->assertSame('/uploads/paquetes/test.jpg', $arr['imagen_principal']);
    }

    public function test_toArray_imagen_principal_null_cuando_no_hay_imagen(): void
    {
        $arr = $this->paquete->toArray();

        $this->assertArrayHasKey('imagen_principal', $arr);
        $this->assertNull($arr['imagen_principal']);
    }

    public function test_constructor_con_imagen_principal_e_imagen_secundaria(): void
    {
        $paquete = new Paquete(
            nombre: 'Paquete con Imagenes',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: null,
            precio: '100',
            disponible: true,
            creadoPor: $this->usuario,
            imagenPrincipal: '/uploads/paquetes/principal.jpg',
            imagenSecundaria: '/uploads/paquetes/secundaria.jpg',
        );

        $this->assertSame('/uploads/paquetes/principal.jpg', $paquete->imagenPrincipal());
        $this->assertSame('/uploads/paquetes/secundaria.jpg', $paquete->imagenSecundaria());
    }

    public function test_constructor_sin_imagen_secundaria_por_defecto(): void
    {
        $this->assertNull($this->paquete->imagenSecundaria());
    }

    public function test_update_agrega_imagen_secundaria(): void
    {
        $this->paquete->update(
            nombre: 'Paquete Modificado',
            descripcion: 'Nueva descripción',
            fechaPartida: new \DateTimeImmutable('2026-08-01'),
            fechaVuelta: new \DateTimeImmutable('2026-08-10'),
            precio: '2000.00',
            disponible: false,
            actualizadoPor: $this->usuario,
            imagenSecundaria: '/uploads/paquetes/secundaria.jpg',
        );

        $this->assertSame('/uploads/paquetes/secundaria.jpg', $this->paquete->imagenSecundaria());
    }

    public function test_update_conserva_imagen_secundaria_si_no_se_envia(): void
    {
        $paquete = new Paquete(
            nombre: 'Test',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: null,
            precio: '100',
            disponible: true,
            creadoPor: $this->usuario,
            imagenSecundaria: '/uploads/paquetes/existente.jpg',
        );

        $paquete->update(
            nombre: 'Test Actualizado',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: null,
            precio: '100',
            disponible: true,
            actualizadoPor: $this->usuario,
        );

        $this->assertSame('/uploads/paquetes/existente.jpg', $paquete->imagenSecundaria());
    }

    public function test_update_reemplaza_imagen_secundaria(): void
    {
        $paquete = new Paquete(
            nombre: 'Test',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: null,
            precio: '100',
            disponible: true,
            creadoPor: $this->usuario,
            imagenSecundaria: '/uploads/paquetes/vieja.jpg',
        );

        $paquete->update(
            nombre: 'Test',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: null,
            precio: '100',
            disponible: true,
            actualizadoPor: $this->usuario,
            imagenSecundaria: '/uploads/paquetes/nueva.jpg',
        );

        $this->assertSame('/uploads/paquetes/nueva.jpg', $paquete->imagenSecundaria());
    }

    public function test_toArray_incluye_imagen_secundaria(): void
    {
        $paquete = new Paquete(
            nombre: 'Test',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: null,
            precio: '100',
            disponible: true,
            creadoPor: $this->usuario,
            imagenSecundaria: '/uploads/paquetes/secundaria.jpg',
        );

        $arr = $paquete->toArray();

        $this->assertArrayHasKey('imagen_secundaria', $arr);
        $this->assertSame('/uploads/paquetes/secundaria.jpg', $arr['imagen_secundaria']);
    }

    public function test_toArray_imagen_secundaria_null_cuando_no_hay_imagen(): void
    {
        $arr = $this->paquete->toArray();

        $this->assertArrayHasKey('imagen_secundaria', $arr);
        $this->assertNull($arr['imagen_secundaria']);
    }

    public function test_constructor_lanza_excepcion_si_nombre_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('nombre es obligatorio');

        new Paquete('', 'desc', new \DateTimeImmutable(), null, '100', true, $this->usuario);
    }

    public function test_constructor_lanza_excepcion_si_precio_no_numerico(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('precio debe ser un valor numérico');

        new Paquete('Test', 'desc', new \DateTimeImmutable(), null, 'abc', true, $this->usuario);
    }

    public function test_constructor_lanza_excepcion_si_precio_negativo(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('precio debe ser un valor numérico');

        new Paquete('Test', 'desc', new \DateTimeImmutable(), null, '-50', true, $this->usuario);
    }
}
