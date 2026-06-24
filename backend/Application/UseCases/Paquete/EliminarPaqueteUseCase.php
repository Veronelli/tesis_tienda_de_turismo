<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Paquete;

use TiendaTurismo\GestionDatos\Domain\Repositories\PaqueteRepositoryInterface;
use TiendaTurismo\GestionDatos\Domain\Repositories\UsuarioRepositoryInterface;

final class EliminarPaqueteUseCase
{
    public function __construct(
        private readonly PaqueteRepositoryInterface $paquetes,
        private readonly UsuarioRepositoryInterface $usuarios,
    ) {
    }

    public function execute(int $id, int $usuarioResponsableId): void
    {
        $usuario = $this->usuarios->findById($usuarioResponsableId);

        if ($usuario === null) {
            throw new \RuntimeException('Usuario responsable no encontrado.');
        }

        $paquete = $this->paquetes->findById($id);

        if ($paquete === null) {
            throw new \RuntimeException('Paquete no encontrado.');
        }

        $this->paquetes->delete($paquete);
    }
}
