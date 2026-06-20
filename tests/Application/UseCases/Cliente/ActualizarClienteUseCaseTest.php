<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Cliente;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\Input\ActualizarClienteInput;
use TiendaTurismo\GestionDatos\Application\UseCases\Cliente\ActualizarClienteUseCase;
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
