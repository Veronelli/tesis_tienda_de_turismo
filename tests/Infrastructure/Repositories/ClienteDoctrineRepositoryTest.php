<?php
declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Infrastructure\Repositories;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Domain\Models\Cliente;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\ClienteDoctrineRepository;
use TiendaTurismo\GestionDatos\Tests\Shared\EntityManagerMockFactory;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\ClienteFixtures;

final class ClienteDoctrineRepositoryTest extends TestCase
{
    use EntityManagerMockFactory;

    private ClienteDoctrineRepository $repo;
    private Cliente $cliente;

    protected function setUp(): void
    {
        $this->crearMocksEntityManager($this, Cliente::class);

        $this->repo = new ClienteDoctrineRepository($this->entityManager);

        $this->cliente = ClienteFixtures::clienteSinId();
    }

    public function test_save_persists_and_flushes(): void
    {
        $this->entityManager->expects($this->once())->method('persist')->with($this->cliente);
        $this->entityManager->expects($this->once())->method('flush');

        $this->repo->save($this->cliente);
    }

    public function test_update_flushes(): void
    {
        $this->entityManager->expects($this->once())->method('flush');

        $this->repo->update($this->cliente);
    }

    public function test_findById_delega_en_entityManager(): void
    {
        $clienteConId = ClienteFixtures::clienteValido();

        $this->entityManager
            ->expects($this->once())
            ->method('find')
            ->with(Cliente::class, 5)
            ->willReturn($clienteConId);

        $resultado = $this->repo->findById(5);

        $this->assertSame($clienteConId, $resultado);
    }

    public function test_findById_retorna_null_si_no_existe(): void
    {
        $this->entityManager
            ->method('find')
            ->with(Cliente::class, 999)
            ->willReturn(null);

        $this->assertNull($this->repo->findById(999));
    }

    public function test_findByDni_retorna_cliente_por_dni(): void
    {
        $cliente = ClienteFixtures::clienteValido();

        $this->entityRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['dni' => '12345678'])
            ->willReturn($cliente);

        $resultado = $this->repo->findByDni('12345678');

        $this->assertSame($cliente, $resultado);
    }

    public function test_findAll_retorna_lista_de_clientes(): void
    {
        $clientes = [$this->cliente];

        $this->entityRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($clientes);

        $resultado = $this->repo->findAll();

        $this->assertSame($clientes, $resultado);
    }
}
