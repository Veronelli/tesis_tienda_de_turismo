<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Cliente;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Cliente\ListarClientesUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\ClienteRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\ClienteFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\ClienteRepositoryMockTrait;

final class ListarClientesUseCaseTest extends TestCase
{
    use ClienteRepositoryMockTrait;

    private ClienteRepositoryInterface $clienteRepo;
    private ListarClientesUseCase $useCase;

    protected function setUp(): void
    {
        $this->clienteRepo = $this->createClienteRepositoryMock();
        $this->useCase = new ListarClientesUseCase($this->clienteRepo);
    }

    public function test_execute_retorna_lista_de_clientes(): void
    {
        $clientes = [ClienteFixtures::clienteValido()];

        $this->clienteRepo
            ->method('findAll')
            ->willReturn($clientes);

        $resultado = $this->useCase->execute();

        $this->assertCount(1, $resultado);
        $this->assertSame($clientes, $resultado);
    }

    public function test_execute_retorna_lista_vacia(): void
    {
        $this->clienteRepo
            ->method('findAll')
            ->willReturn([]);

        $resultado = $this->useCase->execute();

        $this->assertCount(0, $resultado);
        $this->assertSame([], $resultado);
    }
}
