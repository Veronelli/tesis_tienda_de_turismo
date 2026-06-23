<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Paquete;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\Input\CrearPaqueteInput;
use TiendaTurismo\GestionDatos\Application\UseCases\Paquete\CrearPaqueteUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\HotelRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\UsuarioRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\PaqueteFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\HotelRepositoryMockTrait;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\PaqueteRepositoryMockTrait;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\UsuarioRepositoryMockTrait;

final class CrearPaqueteUseCaseTest extends TestCase
{
    use PaqueteRepositoryMockTrait;
    use HotelRepositoryMockTrait;
    use UsuarioRepositoryMockTrait;

    private PaqueteRepositoryInterface $paqueteRepo;
    private HotelRepositoryInterface $hotelRepo;
    private UsuarioRepositoryInterface $usuarioRepo;
    private CrearPaqueteUseCase $useCase;

    protected function setUp(): void
    {
        $this->paqueteRepo = $this->createPaqueteRepositoryMock();
        $this->hotelRepo = $this->createHotelRepositoryMock();
        $this->usuarioRepo = $this->createUsuarioRepositoryMock();
        $this->useCase = new CrearPaqueteUseCase($this->paqueteRepo, $this->hotelRepo, $this->usuarioRepo);
    }

    public function test_execute_crea_y_guarda_paquete_con_hotel_y_usuario(): void
    {
        $usuario = PaqueteFixtures::usuarioAdmin();
        $hotel = PaqueteFixtures::hotelUno();

        $this->usuarioRepo
            ->method('findById')
            ->with(1)
            ->willReturn($usuario);

        $this->hotelRepo
            ->method('findById')
            ->with(1)
            ->willReturn($hotel);

        $this->paqueteRepo->expects($this->once())->method('save');
        $this->usuarioRepo->expects($this->never())->method('save');
        $this->hotelRepo->expects($this->never())->method('save');

        $input = new CrearPaqueteInput(
            nombre: 'Paquete Test',
            descripcion: 'Descripción',
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: new \DateTimeImmutable('2026-07-22'),
            precio: '1500.00',
            disponible: true,
            usuarioResponsableId: 1,
            hotelesIds: [1],
        );

        $paquete = $this->useCase->execute($input);

        $this->assertSame('Paquete Test', $paquete->nombre());
        $this->assertSame($usuario, $paquete->creadoPor());
        $this->assertCount(1, $paquete->hoteles());
    }

    public function test_execute_lanza_excepcion_si_usuario_no_existe(): void
    {
        $this->usuarioRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Usuario responsable no encontrado.');

        $input = new CrearPaqueteInput(
            nombre: 'Test', descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-07-15'), fechaVuelta: null,
            precio: '100', disponible: true, usuarioResponsableId: 999, hotelesIds: [1],
        );

        $this->useCase->execute($input);
    }

    public function test_execute_lanza_excepcion_sin_hoteles(): void
    {
        $this->usuarioRepo
            ->method('findById')
            ->with(1)
            ->willReturn(PaqueteFixtures::usuarioAdmin());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Debe seleccionar al menos un hotel.');

        $input = new CrearPaqueteInput(
            nombre: 'Test', descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-07-15'), fechaVuelta: null,
            precio: '100', disponible: true, usuarioResponsableId: 1, hotelesIds: [],
        );

        $this->useCase->execute($input);
    }

    public function test_execute_lanza_excepcion_si_hotel_no_existe(): void
    {
        $this->usuarioRepo
            ->method('findById')
            ->with(1)
            ->willReturn(PaqueteFixtures::usuarioAdmin());

        $this->hotelRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El hotel con ID 999 no existe.');

        $input = new CrearPaqueteInput(
            nombre: 'Test', descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-07-15'), fechaVuelta: null,
            precio: '100', disponible: true, usuarioResponsableId: 1, hotelesIds: [999],
        );

        $this->useCase->execute($input);
    }

    public function test_execute_guarda_paquete_con_multiples_hoteles(): void
    {
        $usuario = PaqueteFixtures::usuarioAdmin();
        $hotel1 = PaqueteFixtures::hotelUno();
        $hotel2 = PaqueteFixtures::hotelDos();

        $this->usuarioRepo
            ->method('findById')
            ->with(1)
            ->willReturn($usuario);

        $this->hotelRepo
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnMap([
                [1, $hotel1],
                [2, $hotel2],
            ]);

        $this->paqueteRepo->expects($this->once())->method('save');
        $this->usuarioRepo->expects($this->never())->method('save');
        $this->hotelRepo->expects($this->never())->method('save');

        $input = new CrearPaqueteInput(
            nombre: 'Paquete Multi Hotel',
            descripcion: 'Con dos hoteles',
            fechaPartida: new \DateTimeImmutable('2026-08-01'),
            fechaVuelta: new \DateTimeImmutable('2026-08-10'),
            precio: '2500.00',
            disponible: true,
            usuarioResponsableId: 1,
            hotelesIds: [1, 2],
        );

        $paquete = $this->useCase->execute($input);

        $this->assertCount(2, $paquete->hoteles());
        $this->assertSame($usuario, $paquete->creadoPor());
    }

    public function test_execute_no_crea_usuario_nuevo(): void
    {
        $usuario = PaqueteFixtures::usuarioAdmin();
        $hotel = PaqueteFixtures::hotelUno();

        $this->usuarioRepo
            ->method('findById')
            ->with(1)
            ->willReturn($usuario);

        $this->hotelRepo
            ->method('findById')
            ->with(1)
            ->willReturn($hotel);

        $this->usuarioRepo->expects($this->never())->method('save');
        $this->paqueteRepo->expects($this->once())->method('save');

        $input = new CrearPaqueteInput(
            nombre: 'Paquete Test',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: null,
            precio: '1500.00',
            disponible: true,
            usuarioResponsableId: 1,
            hotelesIds: [1],
        );

        $this->useCase->execute($input);
    }

    public function test_execute_no_crea_hotel_nuevo(): void
    {
        $usuario = PaqueteFixtures::usuarioAdmin();
        $hotel = PaqueteFixtures::hotelUno();

        $this->usuarioRepo
            ->method('findById')
            ->with(1)
            ->willReturn($usuario);

        $this->hotelRepo
            ->method('findById')
            ->with(1)
            ->willReturn($hotel);

        $this->hotelRepo->expects($this->never())->method('save');
        $this->paqueteRepo->expects($this->once())->method('save');

        $input = new CrearPaqueteInput(
            nombre: 'Paquete Test',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: null,
            precio: '1500.00',
            disponible: true,
            usuarioResponsableId: 1,
            hotelesIds: [1],
        );

        $this->useCase->execute($input);
    }

    public function test_execute_crea_paquete_con_imagen(): void
    {
        $usuario = PaqueteFixtures::usuarioAdmin();
        $hotel = PaqueteFixtures::hotelUno();

        $this->usuarioRepo
            ->method('findById')
            ->with(1)
            ->willReturn($usuario);

        $this->hotelRepo
            ->method('findById')
            ->with(1)
            ->willReturn($hotel);

        $this->paqueteRepo->expects($this->once())->method('save');
        $this->usuarioRepo->expects($this->never())->method('save');
        $this->hotelRepo->expects($this->never())->method('save');

        $input = new CrearPaqueteInput(
            nombre: 'Paquete Con Imagen',
            descripcion: 'Con imagen',
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: new \DateTimeImmutable('2026-07-22'),
            precio: '2000.00',
            disponible: true,
            usuarioResponsableId: 1,
            hotelesIds: [1],
            imagenPrincipal: 'uploads/paquetes/imagen.jpg',
        );

        $paquete = $this->useCase->execute($input);

        $this->assertSame('uploads/paquetes/imagen.jpg', $paquete->imagenPrincipal());
    }

    public function test_execute_crea_paquete_sin_imagen(): void
    {
        $usuario = PaqueteFixtures::usuarioAdmin();
        $hotel = PaqueteFixtures::hotelUno();

        $this->usuarioRepo
            ->method('findById')
            ->with(1)
            ->willReturn($usuario);

        $this->hotelRepo
            ->method('findById')
            ->with(1)
            ->willReturn($hotel);

        $this->paqueteRepo->expects($this->once())->method('save');
        $this->usuarioRepo->expects($this->never())->method('save');
        $this->hotelRepo->expects($this->never())->method('save');

        $input = new CrearPaqueteInput(
            nombre: 'Paquete Sin Imagen',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-07-15'),
            fechaVuelta: null,
            precio: '1000.00',
            disponible: true,
            usuarioResponsableId: 1,
            hotelesIds: [1],
        );

        $paquete = $this->useCase->execute($input);

        $this->assertNull($paquete->imagenPrincipal());
    }

    public function test_execute_verifica_destino_a_traves_del_hotel(): void
    {
        $usuario = PaqueteFixtures::usuarioAdmin();
        $hotel = PaqueteFixtures::hotelUno();

        $this->usuarioRepo
            ->method('findById')
            ->with(1)
            ->willReturn($usuario);

        $this->hotelRepo
            ->method('findById')
            ->with(1)
            ->willReturn($hotel);

        $this->paqueteRepo->expects($this->once())->method('save');
        $this->usuarioRepo->expects($this->never())->method('save');
        $this->hotelRepo->expects($this->never())->method('save');

        $input = new CrearPaqueteInput(
            nombre: 'Paquete Destino',
            descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-09-01'),
            fechaVuelta: null,
            precio: '800.00',
            disponible: true,
            usuarioResponsableId: 1,
            hotelesIds: [1],
        );

        $paquete = $this->useCase->execute($input);
        $hotelDelPaquete = $paquete->hoteles()->first();

        $this->assertNotNull($hotelDelPaquete);
        $this->assertSame('Buenos Aires', $hotelDelPaquete->destino()->ciudad());
        $this->assertSame('Argentina', $hotelDelPaquete->destino()->pais());
    }
}
