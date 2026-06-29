<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Consulta;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\Input\CrearConsultaInput;
use TiendaTurismo\GestionDatos\Application\AI\Contracts\ProspectoCalificadorInterface;
use TiendaTurismo\GestionDatos\Application\UseCases\Consulta\CrearConsultaUseCase;
use TiendaTurismo\GestionDatos\Domain\Exceptions\DuplicadoException;
use TiendaTurismo\GestionDatos\Domain\Models\Consulta;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\ConsultaRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\ClienteFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\ConsultaFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\PaqueteFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\ClienteRepositoryMockTrait;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\ConsultaRepositoryMockTrait;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\PaqueteRepositoryMockTrait;

final class CrearConsultaUseCaseTest extends TestCase
{
    use ConsultaRepositoryMockTrait;
    use ClienteRepositoryMockTrait;
    use PaqueteRepositoryMockTrait;

    private ConsultaRepositoryInterface $consultaRepo;
    private ClienteRepositoryInterface $clienteRepo;
    private PaqueteRepositoryInterface $paqueteRepo;
    private ProspectoCalificadorInterface $enviarProspecto;
    private CrearConsultaUseCase $useCase;

    protected function setUp(): void
    {
        $this->consultaRepo = $this->createConsultaRepositoryMock();
        $this->clienteRepo = $this->createClienteRepositoryMock();
        $this->paqueteRepo = $this->createPaqueteRepositoryMock();
        $this->enviarProspecto = $this->createMock(ProspectoCalificadorInterface::class);
        $this->useCase = new CrearConsultaUseCase(
            $this->consultaRepo,
            $this->clienteRepo,
            $this->paqueteRepo,
            $this->enviarProspecto,
        );
    }

    public function test_execute_crea_consulta_con_cliente_existente(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();
        $cliente = ConsultaFixtures::clienteValido();

        $this->paqueteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($paquete);

        $this->clienteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($cliente);

        $this->enviarProspecto
            ->expects($this->once())
            ->method('execute')
            ->with(
                'Quiero más información.',
                $this->callback(static fn (string $context): bool => str_contains($context, 'paquete_id=1') && str_contains($context, 'cliente_id=1')),
            )
            ->willReturn(['calificacion' => 'CALIENTE']);

        $this->consultaRepo->expects($this->once())->method('save');

        $input = new CrearConsultaInput(
            paqueteId: 1,
            mensaje: 'Quiero más información.',
            clienteId: 1,
        );

        $consulta = $this->useCase->execute($input);

        $this->assertSame($cliente, $consulta->cliente());
        $this->assertSame($paquete, $consulta->paquete());
        $this->assertSame('Quiero más información.', $consulta->mensaje());
        $this->assertSame(Consulta::ESTADO_PENDIENTE, $consulta->estado());
        $this->assertSame('Caliente', $consulta->calificacion());
    }

    public function test_execute_crea_consulta_creando_cliente_nuevo(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();

        $this->paqueteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($paquete);

        $this->clienteRepo
            ->method('findByEmail')
            ->with('nuevo@example.com')
            ->willReturn(null);

        $this->enviarProspecto
            ->expects($this->once())
            ->method('execute')
            ->with(
                'Consulta desde nuevo cliente.',
                $this->callback(static fn (string $context): bool => str_contains($context, 'paquete_id=1') && str_contains($context, 'cliente_id=nuevo') && str_contains($context, 'datos_cliente=Nuevo Cliente nuevo@example.com La Plata')),
            )
            ->willReturn(['calificacion' => 'TIBIO']);

        $this->clienteRepo->expects($this->once())->method('save');
        $this->consultaRepo->expects($this->once())->method('save');

        $input = new CrearConsultaInput(
            paqueteId: 1,
            mensaje: 'Consulta desde nuevo cliente.',
            datosCliente: [
                'nombre' => 'Nuevo',
                'apellido' => 'Cliente',
                'email' => 'nuevo@example.com',
                'telefono' => '111111111',
                'dni' => '99999999',
                'ubicacion' => 'La Plata',
            ],
        );

        $consulta = $this->useCase->execute($input);

        $this->assertSame('Nuevo', $consulta->cliente()->nombre());
        $this->assertSame('nuevo@example.com', $consulta->cliente()->email());
        $this->assertSame(Consulta::CALIFICACION_TIBIO, $consulta->calificacion());
    }

    public function test_execute_reusa_cliente_existente_por_email(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();
        $clienteExistente = ConsultaFixtures::clienteValido();

        $this->paqueteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($paquete);

        $this->clienteRepo
            ->method('findByEmail')
            ->with('juan@example.com')
            ->willReturn($clienteExistente);

        $this->enviarProspecto
            ->expects($this->once())
            ->method('execute')
            ->with(
                'Consulta reusando cliente.',
                $this->callback(static fn (string $context): bool => str_contains($context, 'paquete_id=1') && str_contains($context, 'cliente_id=1') && str_contains($context, 'datos_cliente=Juan Pérez juan@example.com Buenos Aires')),
            )
            ->willReturn(['calificacion' => 'FRIO']);

        $this->clienteRepo->expects($this->never())->method('save');
        $this->consultaRepo->expects($this->once())->method('save');

        $input = new CrearConsultaInput(
            paqueteId: 1,
            mensaje: 'Consulta reusando cliente.',
            datosCliente: [
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'email' => 'juan@example.com',
                'telefono' => '123456789',
                'dni' => '12345678',
                'ubicacion' => 'Buenos Aires',
            ],
        );

        $consulta = $this->useCase->execute($input);

        $this->assertSame($clienteExistente, $consulta->cliente());
        $this->assertSame('juan@example.com', $consulta->cliente()->email());
        $this->assertSame(Consulta::CALIFICACION_FRIO, $consulta->calificacion());
    }

    public function test_execute_reusa_cliente_existente_por_dni(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();
        $clienteExistente = ConsultaFixtures::clienteValido();

        $this->paqueteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($paquete);

        $this->clienteRepo
            ->method('findByEmail')
            ->with('nuevo@example.com')
            ->willReturn(null);

        $this->clienteRepo
            ->method('findByDni')
            ->with('12345678')
            ->willReturn($clienteExistente);

        $this->enviarProspecto
            ->expects($this->once())
            ->method('execute')
            ->with(
                'Consulta reusando por DNI.',
                $this->callback(static fn (string $context): bool => str_contains($context, 'paquete_id=1') && str_contains($context, 'cliente_id=1') && str_contains($context, 'datos_cliente=Nuevo Cliente nuevo@example.com La Plata')),
            )
            ->willReturn(['calificacion' => 'CALIENTE']);

        $this->clienteRepo->expects($this->never())->method('save');
        $this->consultaRepo->expects($this->once())->method('save');

        $input = new CrearConsultaInput(
            paqueteId: 1,
            mensaje: 'Consulta reusando por DNI.',
            datosCliente: [
                'nombre' => 'Nuevo',
                'apellido' => 'Cliente',
                'email' => 'nuevo@example.com',
                'telefono' => '111111111',
                'dni' => '12345678',
                'ubicacion' => 'La Plata',
            ],
        );

        $consulta = $this->useCase->execute($input);

        $this->assertSame($clienteExistente, $consulta->cliente());
        $this->assertSame('juan@example.com', $consulta->cliente()->email());
    }

    public function test_execute_reusa_cliente_cuando_email_y_dni_coinciden_mismo_cliente(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();
        $clienteExistente = ConsultaFixtures::clienteValido();

        $this->paqueteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($paquete);

        $this->clienteRepo
            ->method('findByEmail')
            ->with('juan@example.com')
            ->willReturn($clienteExistente);

        $this->clienteRepo
            ->method('findByDni')
            ->with('12345678')
            ->willReturn($clienteExistente);

        $this->enviarProspecto
            ->expects($this->once())
            ->method('execute')
            ->with(
                'Consulta mismo cliente.',
                $this->callback(static fn (string $context): bool => str_contains($context, 'paquete_id=1') && str_contains($context, 'cliente_id=1') && str_contains($context, 'datos_cliente=Juan Pérez juan@example.com Buenos Aires')),
            )
            ->willReturn(['calificacion' => 'TIBIO']);

        $this->clienteRepo->expects($this->never())->method('save');
        $this->consultaRepo->expects($this->once())->method('save');

        $input = new CrearConsultaInput(
            paqueteId: 1,
            mensaje: 'Consulta mismo cliente.',
            datosCliente: [
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'email' => 'juan@example.com',
                'telefono' => '123456789',
                'dni' => '12345678',
                'ubicacion' => 'Buenos Aires',
            ],
        );

        $consulta = $this->useCase->execute($input);

        $this->assertSame($clienteExistente, $consulta->cliente());
        $this->assertSame('juan@example.com', $consulta->cliente()->email());
    }

    public function test_execute_lanza_excepcion_si_email_y_dni_pertenecen_a_clientes_distintos(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();
        $clienteA = ClienteFixtures::clienteValido();
        $clienteB = ClienteFixtures::otroClienteValido();

        $this->paqueteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($paquete);

        $this->clienteRepo
            ->method('findByEmail')
            ->with('juan@example.com')
            ->willReturn($clienteA);

        $this->clienteRepo
            ->method('findByDni')
            ->with('87654321')
            ->willReturn($clienteB);

        $this->enviarProspecto->expects($this->never())->method('execute');
        $this->clienteRepo->expects($this->never())->method('save');
        $this->consultaRepo->expects($this->never())->method('save');

        $this->expectException(DuplicadoException::class);
        $this->expectExceptionMessage('El email y el DNI pertenecen a clientes distintos.');

        $input = new CrearConsultaInput(
            paqueteId: 1,
            mensaje: 'Consulta con conflicto.',
            datosCliente: [
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'email' => 'juan@example.com',
                'telefono' => '123456789',
                'dni' => '87654321',
                'ubicacion' => 'Buenos Aires',
            ],
        );

        $this->useCase->execute($input);
    }

    public function test_execute_lanza_excepcion_si_paquete_no_existe(): void
    {
        $this->paqueteRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El paquete con ID 999 no existe.');

        $this->enviarProspecto->expects($this->never())->method('execute');

        $input = new CrearConsultaInput(
            paqueteId: 999,
            mensaje: 'Consulta sin paquete.',
            clienteId: 1,
        );

        $this->useCase->execute($input);
    }

    public function test_execute_lanza_excepcion_si_cliente_no_existe(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();

        $this->paqueteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($paquete);

        $this->clienteRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El cliente con ID 999 no existe.');

        $this->enviarProspecto->expects($this->never())->method('execute');

        $input = new CrearConsultaInput(
            paqueteId: 1,
            mensaje: 'Consulta sin cliente.',
            clienteId: 999,
        );

        $this->useCase->execute($input);
    }

    public function test_execute_lanza_excepcion_sin_cliente_y_sin_datos(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();

        $this->paqueteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($paquete);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Debe proporcionar un cliente_id o datos completos del cliente.');

        $this->enviarProspecto->expects($this->never())->method('execute');

        $input = new CrearConsultaInput(
            paqueteId: 1,
            mensaje: 'Consulta sin datos de cliente.',
        );

        $this->useCase->execute($input);
    }
}
