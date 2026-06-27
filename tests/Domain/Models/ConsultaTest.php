<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Domain\Models;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Domain\Models\Cliente;
use TiendaTurismo\GestionDatos\Domain\Models\Consulta;
use TiendaTurismo\GestionDatos\Domain\Models\Paquete;
use TiendaTurismo\GestionDatos\Domain\Models\Usuario;

final class ConsultaTest extends TestCase
{
    private Cliente $cliente;
    private Paquete $paquete;
    private Consulta $consulta;

    protected function setUp(): void
    {
        $usuario = new Usuario('Admin', 'Test', 'admin@test.com', 'hash', 'admin', id: 1);
        $this->cliente = new Cliente(
            'Juan', 'Pérez', 'juan@example.com', '123456789', '12345678', 'Buenos Aires', id: 1,
        );
        $this->paquete = new Paquete(
            nombre: 'Paquete Test',
            descripcion: 'Descripción test',
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: new \DateTimeImmutable('2026-07-22'),
            precio: '1500.00',
            disponible: true,
            creadoPor: $usuario,
        );

        $this->consulta = new Consulta(
            cliente: $this->cliente,
            paquete: $this->paquete,
            mensaje: 'Quiero información sobre este paquete.',
            calificacion: Consulta::CALIFICACION_CALIENTE,
            creadoPor: $usuario,
        );
    }

    public function test_constructor_asigna_valores_correctamente(): void
    {
        $this->assertSame($this->cliente, $this->consulta->cliente());
        $this->assertSame($this->paquete, $this->consulta->paquete());
        $this->assertSame('Quiero información sobre este paquete.', $this->consulta->mensaje());
        $this->assertSame(Consulta::ESTADO_PENDIENTE, $this->consulta->estado());
        $this->assertSame(Consulta::CALIFICACION_CALIENTE, $this->consulta->calificacion());
        $this->assertSame('admin@test.com', $this->consulta->creadoPor()?->email());
        $this->assertNotNull($this->consulta->fechaConsulta());
    }

    public function test_id_es_null_para_nueva_consulta(): void
    {
        $this->assertNull($this->consulta->id());
    }

    public function test_update_cambia_estado(): void
    {
        $this->consulta->update(estado: Consulta::ESTADO_PROCESANDO);
        $this->assertSame(Consulta::ESTADO_PROCESANDO, $this->consulta->estado());
    }

    public function test_update_cambia_calificacion(): void
    {
        $this->consulta->update(calificacion: Consulta::CALIFICACION_TIBIO);
        $this->assertSame(Consulta::CALIFICACION_TIBIO, $this->consulta->calificacion());
    }

    public function test_update_cambia_mensaje(): void
    {
        $this->consulta->update(mensaje: 'Nuevo mensaje');
        $this->assertSame('Nuevo mensaje', $this->consulta->mensaje());
    }

    public function test_update_cambia_cliente(): void
    {
        $otroCliente = new Cliente(
            'María', 'García', 'maria@example.com', '987654321', '87654321', 'Córdoba', id: 2,
        );
        $this->consulta->update(cliente: $otroCliente);
        $this->assertSame($otroCliente, $this->consulta->cliente());
    }

    public function test_update_cambia_paquete(): void
    {
        $usuario = new Usuario('Admin', 'Test', 'admin@test.com', 'hash', 'admin', id: 1);
        $otroPaquete = new Paquete(
            nombre: 'Otro Paquete',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-08-01'),
            fechaVuelta: null,
            precio: '2000.00',
            disponible: true,
            creadoPor: $usuario,
        );
        $this->consulta->update(paquete: $otroPaquete);
        $this->assertSame($otroPaquete, $this->consulta->paquete());
    }

    public function test_update_cambia_usuario_actualizacion(): void
    {
        $usuarioEditor = new Usuario('Editor', 'Test', 'editor@test.com', 'hash', 'editor', id: 2);

        $this->consulta->update(actualizadoPor: $usuarioEditor);

        $this->assertSame('admin@test.com', $this->consulta->creadoPor()?->email());
        $this->assertSame($usuarioEditor, $this->consulta->actualizadoPor());

        $arr = $this->consulta->toArray();
        $this->assertSame(1, $arr['creado_por']['id']);
        $this->assertSame('admin@test.com', $arr['creado_por']['email']);
        $this->assertSame(2, $arr['actualizado_por']['id']);
        $this->assertSame('editor@test.com', $arr['actualizado_por']['email']);
    }

    public function test_update_con_estado_invalido_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->consulta->update(estado: 'invalido');
    }

    public function test_constructor_lanza_excepcion_si_mensaje_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Consulta($this->cliente, $this->paquete, '', Consulta::CALIFICACION_FRIO);
    }

    public function test_toArray_retorna_todos_los_campos(): void
    {
        $arr = $this->consulta->toArray();

        $this->assertArrayHasKey('id', $arr);
        $this->assertArrayHasKey('cliente', $arr);
        $this->assertArrayHasKey('paquete', $arr);
        $this->assertArrayHasKey('mensaje', $arr);
        $this->assertArrayHasKey('estado', $arr);
        $this->assertArrayHasKey('calificacion', $arr);
        $this->assertArrayHasKey('creado_por', $arr);
        $this->assertArrayHasKey('actualizado_por', $arr);
        $this->assertArrayHasKey('fecha_consulta', $arr);
        $this->assertArrayHasKey('fecha_creacion', $arr);
        $this->assertArrayHasKey('fecha_actualizacion', $arr);
    }

    public function test_toArray_retorna_valores_correctos(): void
    {
        $arr = $this->consulta->toArray();

        $this->assertNull($arr['id']);
        $this->assertSame('Quiero información sobre este paquete.', $arr['mensaje']);
        $this->assertSame(Consulta::ESTADO_PENDIENTE, $arr['estado']);
        $this->assertSame(Consulta::CALIFICACION_CALIENTE, $arr['calificacion']);
        $this->assertSame('admin@test.com', $arr['creado_por']['email']);
        $this->assertNull($arr['actualizado_por']);
        $this->assertSame($this->cliente->id(), $arr['cliente']['id']);
        $this->assertSame($this->paquete->nombre(), $arr['paquete']['nombre']);
    }
}
