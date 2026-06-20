<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Cliente;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\Input\CrearClienteInput;
use TiendaTurismo\GestionDatos\Application\UseCases\Cliente\CrearClienteUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\ClienteRepositoryMockTrait;

final class CrearClienteUseCaseTest extends TestCase
{
    use ClienteRepositoryMockTrait;

    private ClienteRepositoryInterface $clienteRepo;
    private CrearClienteUseCase $useCase;

    protected function setUp(): void
    {
        $this->clienteRepo = $this->createClienteRepositoryMock();
        $this->useCase = new CrearClienteUseCase($this->clienteRepo);
    }

    public function test_execute_crea_y_guarda_cliente(): void
    {
        $this->clienteRepo->expects($this->once())->method('save');

        $input = new CrearClienteInput('Juan', 'Pérez', 'juan@example.com', '123456789', '12345678', 'Buenos Aires');
        $cliente = $this->useCase->execute($input);

        $this->assertSame('Juan', $cliente->nombre());
        $this->assertSame('Pérez', $cliente->apellido());
        $this->assertSame('juan@example.com', $cliente->email());
        $this->assertSame('123456789', $cliente->telefono());
        $this->assertSame('12345678', $cliente->dni());
        $this->assertSame('Buenos Aires', $cliente->ubicacion());
    }

    public function test_execute_lanza_excepcion_si_nombre_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('nombre es obligatorio');

        $input = new CrearClienteInput('', 'Pérez', 'juan@example.com', '123456789', '12345678', 'Buenos Aires');
        $this->useCase->execute($input);
    }

    public function test_execute_lanza_excepcion_si_email_invalido(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('email no es válido');

        $input = new CrearClienteInput('Juan', 'Pérez', 'invalido', '123456789', '12345678', 'Buenos Aires');
        $this->useCase->execute($input);
    }
}
