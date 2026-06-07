<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\UseCases\Usuario;

use TiendaTurismo\GestionDatos\Domain\Exceptions\CredencialesInvalidasException;
use TiendaTurismo\GestionDatos\Domain\Repositories\UsuarioRepositoryInterface;
use TiendaTurismo\GestionDatos\Infrastructure\Security\JwtService;

final class LoginUseCase
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarios,
        private readonly JwtService $jwt,
    ) {
    }

    public function execute(string $email, string $password): string
    {
        $usuario = $this->usuarios->findByEmail($email);

        if ($usuario === null || !password_verify($password, $usuario->contrasena())) {
            throw new CredencialesInvalidasException();
        }

        return $this->jwt->encode([
            'sub' => $usuario->id(),
            'email' => $usuario->email(),
            'rol' => $usuario->rol(),
        ]);
    }
}
