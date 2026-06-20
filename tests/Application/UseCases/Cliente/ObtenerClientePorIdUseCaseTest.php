<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Cliente;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Cliente\ObtenerClientePorIdUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\ClienteFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\ClienteRepositoryMockTrait;

final class ObtenerClientePorIdUseCaseTest extends TestCase
{
    use ClienteRepositoryMockTrait;

    private ClienteRepositoryInterface $clienteRepo;
    private ObtenerClientePorIdUseCase $useCase;

    protected function setUp(): void
    {
        $this->clienteRepo = $this->createClienteRepositoryMock();
        $this->useCase = new ObtenerClientePorIdUseCase($this->clienteRepo);
    }

    public function test_execute_retorna_cliente_por_id(): void
    {
        $cliente = ClienteFixtures::clienteValido();

        $this->clienteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($cliente);

        $resultado = $this->useCase->execute(1);

        $this->assertSame($cliente, $resultado);
    }

    public function test_execute_retorna_null_si_no_existe(): void
    {
        $this->clienteRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $resultado = $this->useCase->execute(999);

        $this->assertNull($resultado);
    }
}
