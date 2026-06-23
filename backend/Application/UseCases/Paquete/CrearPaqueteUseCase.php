<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Paquete;

use TiendaTurismo\GestionDatos\Application\Input\CrearPaqueteInput;
use TiendaTurismo\GestionDatos\Domain\Models\Paquete;
use TiendaTurismo\GestionDatos\Domain\Repositories\HotelRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\UsuarioRepositoryInterface;

final class CrearPaqueteUseCase
{
    public function __construct(
        private readonly PaqueteRepositoryInterface $paquetes,
        private readonly HotelRepositoryInterface $hoteles,
        private readonly UsuarioRepositoryInterface $usuarios,
    ) {
    }

    public function execute(CrearPaqueteInput $input): Paquete
    {
        $usuario = $this->usuarios->findById($input->usuarioResponsableId);

        if ($usuario === null) {
            throw new \RuntimeException('Usuario responsable no encontrado.');
        }

        if (empty($input->hotelesIds)) {
            throw new \InvalidArgumentException('Debe seleccionar al menos un hotel.');
        }

        $hoteles = [];
        foreach ($input->hotelesIds as $id) {
            $hotel = $this->hoteles->findById($id);
            if ($hotel === null) {
                throw new \InvalidArgumentException("El hotel con ID {$id} no existe.");
            }
            $hoteles[] = $hotel;
        }

        $paquete = new Paquete(
            nombre: $input->nombre,
            descripcion: $input->descripcion,
            fechaPartida: $input->fechaPartida,
            fechaVuelta: $input->fechaVuelta,
            precio: $input->precio,
            disponible: $input->disponible,
            creadoPor: $usuario,
            imagenPrincipal: $input->imagenPrincipal,
        );

        $paquete->syncHoteles($hoteles);
        $this->paquetes->save($paquete);

        return $paquete;
    }
}
