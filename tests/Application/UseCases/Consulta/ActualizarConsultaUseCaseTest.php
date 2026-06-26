<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Consulta;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\Input\ActualizarConsultaInput;
use TiendaTurismo\GestionDatos\Application\UseCases\Consulta\ActualizarConsultaUseCase;
use TiendaTurismo\GestionDatos\Domain\Models\Consulta;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\ConsultaRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\UsuarioRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\ConsultaFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\ClienteRepositoryMockTrait;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\ConsultaRepositoryMockTrait;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\PaqueteRepositoryMockTrait;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\UsuarioRepositoryMockTrait;

final class ActualizarConsultaUseCaseTest extends TestCase
{
    use ConsultaRepositoryMockTrait;
    use ClienteRepositoryMockTrait;
    use PaqueteRepositoryMockTrait;
    use UsuarioRepositoryMockTrait;

    private ConsultaRepositoryInterface $consultaRepo;
    private ClienteRepositoryInterface $clienteRepo;
    private PaqueteRepositoryInterface $paqueteRepo;
    private UsuarioRepositoryInterface $usuarioRepo;
    private ActualizarConsultaUseCase $useCase;

    protected function setUp(): void
    {
        $this->consultaRepo = $this->createConsultaRepositoryMock();
        $this->clienteRepo = $this->createClienteRepositoryMock();
        $this->paqueteRepo = $this->createPaqueteRepositoryMock();
        $this->usuarioRepo = $this->createUsuarioRepositoryMock();
        $this->useCase = new ActualizarConsultaUseCase($this->consultaRepo, $this->clienteRepo, $this->paqueteRepo, $this->usuarioRepo);
    }

    public function test_execute_actualiza_estado(): void
    {
        $consulta = ConsultaFixtures::consultaPendiente();
        $usuarioEditor = ConsultaFixtures::usuarioEditor();

        $this->consultaRepo
            ->method('findById')
            ->with(1)
            ->willReturn($consulta);

        $this->consultaRepo->expects($this->once())->method('update');

        $this->usuarioRepo
            ->method('findById')
            ->with(2)
            ->willReturn($usuarioEditor);

        $input = new ActualizarConsultaInput(
            id: 1,
            estado: Consulta::ESTADO_PROCESANDO,
            usuarioResponsableId: 2,
        );

        $resultado = $this->useCase->execute($input);

        $this->assertSame(Consulta::ESTADO_PROCESANDO, $resultado->estado());
        $this->assertSame($usuarioEditor, $resultado->actualizadoPor());
    }

    public function test_execute_lanza_excepcion_si_consulta_no_existe(): void
    {
        $this->consultaRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Consulta no encontrada.');

        $input = new ActualizarConsultaInput(
            id: 999,
            estado: Consulta::ESTADO_CANCELADA,
        );

        $this->useCase->execute($input);
    }

    public function test_execute_lanza_excepcion_si_cliente_no_existe(): void
    {
        $consulta = ConsultaFixtures::consultaPendiente();

        $this->consultaRepo
            ->method('findById')
            ->with(1)
            ->willReturn($consulta);

        $this->clienteRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->usuarioRepo
            ->method('findById')
            ->with(1)
            ->willReturn(ConsultaFixtures::usuarioEditor());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El cliente con ID 999 no existe.');

        $input = new ActualizarConsultaInput(
            id: 1,
            clienteId: 999,
            usuarioResponsableId: 1,
        );

        $this->useCase->execute($input);
    }

    public function test_execute_lanza_excepcion_si_usuario_responsable_no_existe(): void
    {
        $consulta = ConsultaFixtures::consultaPendiente();

        $this->consultaRepo
            ->method('findById')
            ->with(1)
            ->willReturn($consulta);

        $this->usuarioRepo
            ->method('findById')
            ->with(99)
            ->willReturn(null);

        $this->expectException(
            \RuntimeException::class
        );
        $this->expectExceptionMessage('Usuario responsable no encontrado.');

        $input = new ActualizarConsultaInput(
            id: 1,
            usuarioResponsableId: 99,
        );

        $this->useCase->execute($input);
    }
}
