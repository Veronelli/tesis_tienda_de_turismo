<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Cliente;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\Input\ActualizarClienteInput;
use TiendaTurismo\GestionDatos\Application\UseCases\Cliente\ActualizarClienteUseCase;
use TiendaTurismo\GestionDatos\Domain\Exceptions\DuplicadoException;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\ClienteFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\ClienteRepositoryMockTrait;

final class ActualizarClienteUseCaseTest extends TestCase
{
    use ClienteRepositoryMockTrait;

    private ClienteRepositoryInterface $clienteRepo;
    private ActualizarClienteUseCase $useCase;

    protected function setUp(): void
    {
        $this->clienteRepo = $this->createClienteRepositoryMock();
        $this->useCase = new ActualizarClienteUseCase($this->clienteRepo);
    }

    public function test_execute_actualiza_cliente_existente(): void
    {
        $cliente = ClienteFixtures::clienteValido();

        $this->clienteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($cliente);

        $this->clienteRepo->expects($this->once())->method('update')->with($cliente);

        $input = new ActualizarClienteInput(1, 'Ana', 'López', 'ana@example.com', '999', '888', 'Córdoba');
        $resultado = $this->useCase->execute($input);

        $this->assertSame('Ana', $resultado->nombre());
        $this->assertSame('López', $resultado->apellido());
        $this->assertSame('ana@example.com', $resultado->email());
        $this->assertSame('999', $resultado->telefono());
        $this->assertSame('888', $resultado->dni());
        $this->assertSame('Córdoba', $resultado->ubicacion());
    }

    public function test_execute_actualiza_conservando_su_email_y_dni(): void
    {
        $cliente = ClienteFixtures::clienteValido();

        $this->clienteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($cliente);

        $this->clienteRepo
            ->method('findByEmail')
            ->with('juan@example.com')
            ->willReturn($cliente);

        $this->clienteRepo
            ->method('findByDni')
            ->with('12345678')
            ->willReturn($cliente);

        $this->clienteRepo->expects($this->once())->method('update')->with($cliente);

        $input = new ActualizarClienteInput(1, 'Juan', 'Pérez', 'juan@example.com', '123456789', '12345678', 'Buenos Aires');
        $resultado = $this->useCase->execute($input);

        $this->assertSame('juan@example.com', $resultado->email());
        $this->assertSame('12345678', $resultado->dni());
    }

    public function test_execute_lanza_excepcion_si_email_es_de_otro_cliente(): void
    {
        $clienteActual = ClienteFixtures::clienteValido();
        $otroCliente = ClienteFixtures::otroClienteValido();

        $this->clienteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($clienteActual);

        $this->clienteRepo
            ->method('findByEmail')
            ->with('maria@example.com')
            ->willReturn($otroCliente);

        $this->clienteRepo->expects($this->never())->method('update');

        $this->expectException(DuplicadoException::class);
        $this->expectExceptionMessage('Ya existe un cliente con ese email.');

        $input = new ActualizarClienteInput(1, 'Juan', 'Pérez', 'maria@example.com', '123456789', '12345678', 'Buenos Aires');
        $this->useCase->execute($input);
    }

    public function test_execute_lanza_excepcion_si_dni_es_de_otro_cliente(): void
    {
        $clienteActual = ClienteFixtures::clienteValido();
        $otroCliente = ClienteFixtures::otroClienteValido();

        $this->clienteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($clienteActual);

        $this->clienteRepo
            ->method('findByEmail')
            ->with('juan@example.com')
            ->willReturn($clienteActual);

        $this->clienteRepo
            ->method('findByDni')
            ->with('87654321')
            ->willReturn($otroCliente);

        $this->clienteRepo->expects($this->never())->method('update');

        $this->expectException(DuplicadoException::class);
        $this->expectExceptionMessage('Ya existe un cliente con ese DNI.');

        $input = new ActualizarClienteInput(1, 'Juan', 'Pérez', 'juan@example.com', '123456789', '87654321', 'Buenos Aires');
        $this->useCase->execute($input);
    }

    public function test_execute_lanza_excepcion_si_cliente_no_existe(): void
    {
        $this->clienteRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cliente no encontrado.');

        $input = new ActualizarClienteInput(999, 'Ana', 'López', 'ana@example.com', '999', '888', 'Córdoba');
        $this->useCase->execute($input);
    }
}
