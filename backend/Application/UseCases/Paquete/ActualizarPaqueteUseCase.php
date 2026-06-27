<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Paquete;

use TiendaTurismo\GestionDatos\Application\Input\ActualizarPaqueteInput;
use TiendaTurismo\GestionDatos\Domain\Models\Paquete;
use TiendaTurismo\GestionDatos\Domain\Repositories\HotelRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\UsuarioRepositoryInterface;

final class ActualizarPaqueteUseCase
{
    public function __construct(
        private readonly PaqueteRepositoryInterface $paquetes,
        private readonly HotelRepositoryInterface $hoteles,
        private readonly UsuarioRepositoryInterface $usuarios,
    ) {
    }

    public function execute(ActualizarPaqueteInput $input): Paquete
    {
        $paquete = $this->paquetes->findById($input->id);

        if ($paquete === null) {
            throw new \RuntimeException('Paquete no encontrado.');
        }

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

        $paquete->update(
            nombre: $input->nombre,
            descripcion: $input->descripcion,
            fechaPartida: $input->fechaPartida,
            fechaVuelta: $input->fechaVuelta,
            precio: $input->precio,
            disponible: $input->disponible,
            actualizadoPor: $usuario,
            desayuno: $input->desayuno,
            allInclusive: $input->allInclusive,
            pileta: $input->pileta,
            imagenPrincipal: $input->imagenPrincipal,
            imagenSecundaria: $input->imagenSecundaria,
        );

        $paquete->syncHoteles($hoteles);
        $this->paquetes->update($paquete);

        return $paquete;
    }
}
