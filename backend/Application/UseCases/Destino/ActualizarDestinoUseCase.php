<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Destino;

use TiendaTurismo\GestionDatos\Application\Input\ActualizarDestinoInput;
use TiendaTurismo\GestionDatos\Domain\Models\Destino;
use TiendaTurismo\GestionDatos\Domain\Repositories\DestinoRepositoryInterface;

final class ActualizarDestinoUseCase
{
    public function __construct(
        private readonly DestinoRepositoryInterface $destinos,
    ) {
    }

    public function execute(ActualizarDestinoInput $input): Destino
    {
        $destino = $this->destinos->findById($input->id);

        if ($destino === null) {
            throw new \RuntimeException('Destino no encontrado.');
        }

        $destino->update($input->ciudad, $input->estadoProvincia, $input->pais);
        $this->destinos->update($destino);

        return $destino;
    }
}
