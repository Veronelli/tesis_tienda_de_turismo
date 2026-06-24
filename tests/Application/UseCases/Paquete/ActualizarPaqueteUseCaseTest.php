<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\UseCases\Paquete;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\Input\ActualizarPaqueteInput;
use TiendaTurismo\GestionDatos\Application\UseCases\Paquete\ActualizarPaqueteUseCase;
use TiendaTurismo\GestionDatos\Domain\Repositories\HotelRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\UsuarioRepositoryInterface;
use TiendaTurismo\GestionDatos\Tests\Shared\Fixtures\PaqueteFixtures;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\HotelRepositoryMockTrait;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\PaqueteRepositoryMockTrait;
use TiendaTurismo\GestionDatos\Tests\Shared\Mocks\UsuarioRepositoryMockTrait;

final class ActualizarPaqueteUseCaseTest extends TestCase
{
    use PaqueteRepositoryMockTrait;
    use HotelRepositoryMockTrait;
    use UsuarioRepositoryMockTrait;

    private PaqueteRepositoryInterface $paqueteRepo;
    private HotelRepositoryInterface $hotelRepo;
    private UsuarioRepositoryInterface $usuarioRepo;
    private ActualizarPaqueteUseCase $useCase;

    protected function setUp(): void
    {
        $this->paqueteRepo = $this->createPaqueteRepositoryMock();
        $this->hotelRepo = $this->createHotelRepositoryMock();
        $this->usuarioRepo = $this->createUsuarioRepositoryMock();
        $this->useCase = new ActualizarPaqueteUseCase($this->paqueteRepo, $this->hotelRepo, $this->usuarioRepo);
    }

    public function test_execute_actualiza_paquete_existente_con_usuario(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();
        $editor = PaqueteFixtures::usuarioEditor();
        $hotel1 = PaqueteFixtures::hotelUno();
        $hotel2 = PaqueteFixtures::hotelDos();

        $this->paqueteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($paquete);

        $this->usuarioRepo
            ->method('findById')
            ->with(2)
            ->willReturn($editor);

        $this->hotelRepo
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnMap([
                [1, $hotel1],
                [2, $hotel2],
            ]);

        $this->paqueteRepo->expects($this->once())->method('update')->with($paquete);

        $input = new ActualizarPaqueteInput(
            id: 1,
            nombre: 'Paquete Actualizado',
            descripcion: 'Nueva descripción',
            fechaPartida: new \DateTimeImmutable('2026-08-01'),
            fechaVuelta: new \DateTimeImmutable('2026-08-10'),
            precio: '2000.00',
            disponible: false,
            usuarioResponsableId: 2,
            hotelesIds: [1, 2],
        );

        $resultado = $this->useCase->execute($input);

        $this->assertSame('Paquete Actualizado', $resultado->nombre());
        $this->assertSame('2000.00', $resultado->precio());
        $this->assertFalse($resultado->disponible());
        $this->assertSame($editor, $resultado->actualizadoPor());
        $this->assertCount(2, $resultado->hoteles());
    }

    public function test_execute_lanza_excepcion_si_paquete_no_existe(): void
    {
        $this->paqueteRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Paquete no encontrado.');

        $input = new ActualizarPaqueteInput(
            id: 999, nombre: 'Test', descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-08-01'), fechaVuelta: null,
            precio: '100', disponible: true, usuarioResponsableId: 1, hotelesIds: [1],
        );

        $this->useCase->execute($input);
    }

    public function test_execute_lanza_excepcion_si_usuario_no_existe(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();

        $this->paqueteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($paquete);

        $this->usuarioRepo
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Usuario responsable no encontrado.');

        $input = new ActualizarPaqueteInput(
            id: 1, nombre: 'Test', descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-08-01'), fechaVuelta: null,
            precio: '100', disponible: true, usuarioResponsableId: 999, hotelesIds: [1],
        );

        $this->useCase->execute($input);
    }

    public function test_execute_lanza_excepcion_sin_hoteles(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();

        $this->paqueteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($paquete);

        $this->usuarioRepo
            ->method('findById')
            ->with(1)
            ->willReturn(PaqueteFixtures::usuarioAdmin());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Debe seleccionar al menos un hotel.');

        $input = new ActualizarPaqueteInput(
            id: 1, nombre: 'Test', descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-08-01'), fechaVuelta: null,
            precio: '100', disponible: true, usuarioResponsableId: 1, hotelesIds: [],
        );

        $this->useCase->execute($input);
    }

    public function test_execute_actualiza_con_multiples_hoteles(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();
        $editor = PaqueteFixtures::usuarioEditor();
        $hotel1 = PaqueteFixtures::hotelUno();
        $hotel2 = PaqueteFixtures::hotelDos();

        $this->paqueteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($paquete);

        $this->usuarioRepo
            ->method('findById')
            ->with(2)
            ->willReturn($editor);

        $this->hotelRepo
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnMap([
                [1, $hotel1],
                [2, $hotel2],
            ]);

        $this->paqueteRepo->expects($this->once())->method('update');

        $input = new ActualizarPaqueteInput(
            id: 1, nombre: 'Multi Hotel', descripcion: null,
            fechaPartida: new \DateTimeImmutable('2026-09-01'), fechaVuelta: null,
            precio: '3000.00', disponible: true, usuarioResponsableId: 2, hotelesIds: [1, 2],
        );

        $resultado = $this->useCase->execute($input);

        $this->assertCount(2, $resultado->hoteles());
        $this->assertSame($editor, $resultado->actualizadoPor());
    }

    public function test_execute_actualiza_agregando_imagen_secundaria(): void
    {
        $paquete = PaqueteFixtures::paqueteValido();
        $editor = PaqueteFixtures::usuarioEditor();
        $hotel = PaqueteFixtures::hotelUno();

        $this->paqueteRepo
            ->method('findById')
            ->with(1)
            ->willReturn($paquete);

        $this->usuarioRepo
            ->method('findById')
            ->with(2)
            ->willReturn($editor);

        $this->hotelRepo
            ->method('findById')
            ->with(1)
            ->willReturn($hotel);

        $this->paqueteRepo->expects($this->once())->method('update');

        $input = new ActualizarPaqueteInput(
            id: 1,
            nombre: 'Paquete Actualizado',
            descripcion: 'Descripción',
            fechaPartida: new \DateTimeImmutable('2026-08-01'),
            fechaVuelta: new \DateTimeImmutable('2026-08-10'),
            precio: '2000.00',
            disponible: true,
            usuarioResponsableId: 2,
            hotelesIds: [1],
            imagenSecundaria: '/uploads/paquetes/secundaria.jpg',
        );

        $resultado = $this->useCase->execute($input);

        $this->assertSame('/uploads/paquetes/secundaria.jpg', $resultado->imagenSecundaria());
        $this->assertSame($editor, $resultado->actualizadoPor());
        $this->assertCount(1, $resultado->hoteles());
    }

    public function test_execute_actualiza_conserva_imagen_secundaria_si_no_se_envia(): void
    {
        $paquete = PaqueteFixtures::paqueteConImagenSecundaria();
        $editor = PaqueteFixtures::usuarioEditor();
        $hotel = PaqueteFixtures::hotelUno();

        $this->paqueteRepo
            ->method('findById')
            ->with(2)
            ->willReturn($paquete);

        $this->usuarioRepo
            ->method('findById')
            ->with(2)
            ->willReturn($editor);

        $this->hotelRepo
            ->method('findById')
            ->with(1)
            ->willReturn($hotel);

        $this->paqueteRepo->expects($this->once())->method('update');

        $input = new ActualizarPaqueteInput(
            id: 2,
            nombre: 'Paquete Actualizado',
            descripcion: 'Descripción',
            fechaPartida: new \DateTimeImmutable('2026-08-01'),
            fechaVuelta: new \DateTimeImmutable('2026-08-10'),
            precio: '2000.00',
            disponible: true,
            usuarioResponsableId: 2,
            hotelesIds: [1],
        );

        $resultado = $this->useCase->execute($input);

        $this->assertSame('/uploads/paquetes/secundaria.jpg', $resultado->imagenSecundaria());
        $this->assertSame($editor, $resultado->actualizadoPor());
    }

    public function test_execute_actualiza_reemplaza_imagen_secundaria(): void
    {
        $paquete = PaqueteFixtures::paqueteConImagenSecundaria();
        $editor = PaqueteFixtures::usuarioEditor();
        $hotel = PaqueteFixtures::hotelUno();

        $this->paqueteRepo
            ->method('findById')
            ->with(2)
            ->willReturn($paquete);

        $this->usuarioRepo
            ->method('findById')
            ->with(2)
            ->willReturn($editor);

        $this->hotelRepo
            ->method('findById')
            ->with(1)
            ->willReturn($hotel);

        $this->paqueteRepo->expects($this->once())->method('update');

        $input = new ActualizarPaqueteInput(
            id: 2,
            nombre: 'Paquete Actualizado',
            descripcion: 'Descripción',
            fechaPartida: new \DateTimeImmutable('2026-08-01'),
            fechaVuelta: new \DateTimeImmutable('2026-08-10'),
            precio: '2000.00',
            disponible: true,
            usuarioResponsableId: 2,
            hotelesIds: [1],
            imagenSecundaria: '/uploads/paquetes/nueva_secundaria.jpg',
        );

        $resultado = $this->useCase->execute($input);

        $this->assertSame('/uploads/paquetes/nueva_secundaria.jpg', $resultado->imagenSecundaria());
        $this->assertSame($editor, $resultado->actualizadoPor());
    }
}
