<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Consulta;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\Input\CrearConsultaInput;
use TiendaTurismo\GestionDatos\Application\UseCases\Consulta\CrearConsultaUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\ConsultaRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;
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
    private CrearConsultaUseCase $useCase;

    protected function setUp(): void
    {
        $this->consultaRepo = $this->createConsultaRepositoryMock();
        $this->clienteRepo = $this->createClienteRepositoryMock();
        $this->paqueteRepo = $this->createPaqueteRepositoryMock();
        $this->useCase = new CrearConsultaUseCase($this->consultaRepo, $this->clienteRepo, $this->paqueteRepo);
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

        $this->consultaRepo->expects($this->once())->method('save');

        $input = new CrearConsultaInput(
            paqueteId: 1,
            mensaje: 'Quiero más información.',
            calificacion: 'Caliente',
            clienteId: 1,
        );

        $consulta = $this->useCase->execute($input);

        $this->assertSame($cliente, $consulta->cliente());
        $this->assertSame($paquete, $consulta->paquete());
        $this->assertSame('Quiero más información.', $consulta->mensaje());
        $this->assertSame('pendiente', $consulta->estado());
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

        $this->clienteRepo->expects($this->once())->method('save');
        $this->consultaRepo->expects($this->once())->method('save');

        $input = new CrearConsultaInput(
            paqueteId: 1,
            mensaje: 'Consulta desde nuevo cliente.',
            calificacion: 'Frio',
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

        $this->clienteRepo->expects($this->never())->method('save');
        $this->consultaRepo->expects($this->once())->method('save');

        $input = new CrearConsultaInput(
            paqueteId: 1,
            mensaje: 'Consulta reusando cliente.',
            calificacion: 'tibio',
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

    public function test_execute_lanza_excepcion_si_paquete_no_existe(): void
    {
        $this->paqueteRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El paquete con ID 999 no existe.');

        $input = new CrearConsultaInput(
            paqueteId: 999,
            mensaje: 'Consulta sin paquete.',
            calificacion: 'Frio',
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

        $input = new CrearConsultaInput(
            paqueteId: 1,
            mensaje: 'Consulta sin cliente.',
            calificacion: 'Caliente',
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

        $input = new CrearConsultaInput(
            paqueteId: 1,
            mensaje: 'Consulta sin datos de cliente.',
            calificacion: 'Frio',
        );

        $this->useCase->execute($input);
    }
}
