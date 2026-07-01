<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Usuario;

use TiendaTurismo\GestionDatos\Domain\Models\Usuario;
use TiendaTurismo\GestionDatos\Domain\Repositories\UsuarioRepositoryInterface;

final class ObtenerUsuarioPorIdUseCase
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarios,
    ) {
    }

    public function execute(int $id): ?Usuario
    {
        return $this->usuarios->findById($id);
    }
}
