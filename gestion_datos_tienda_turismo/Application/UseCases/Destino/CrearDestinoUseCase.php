<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Destino;

use TiendaTurismo\GestionDatos\Application\Input\CrearDestinoInput;
use TiendaTurismo\GestionDatos\Domain\Models\Destino;
use TiendaTurismo\GestionDatos\Domain\Repositories\DestinoRepositoryInterface;

final class CrearDestinoUseCase
{
    public function __construct(
        private readonly DestinoRepositoryInterface $destinos,
    ) {
    }

    public function execute(CrearDestinoInput $input): Destino
    {
        $destino = new Destino(
            $input->ciudad,
            $input->estadoProvincia,
            $input->pais,
        );

        $this->destinos->save($destino);

        return $destino;
    }
}
