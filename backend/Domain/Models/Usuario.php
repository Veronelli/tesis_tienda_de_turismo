<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Models;

use Doctrine\ORM\Mapping as ORM;
use TiendaTurismo\GestionDatos\Domain\Models\Traits\AtributosBase;


#[ORM\Entity]
#[ORM\Table(name: 'usuarios')]
class Usuario
{
    use AtributosBase;

    public function __construct(
        #[ORM\Column(type: 'string', length: 100)]
        private string $nombre,
        #[ORM\Column(type: 'string', length: 100)]
        private string $apellido,
        #[ORM\Column(type: 'string', length: 255, unique: true)]
        private string $email,
        #[ORM\Column(type: 'string', length: 255)]
        private string $contrasena,
        #[ORM\Column(type: 'string', length: 50)]
        private string $rol,
        ?int $id = null,
        ?\DateTimeImmutable $fechaCreacion = null,
        ?\DateTimeImmutable $fechaActualizacion = null,
    ) {
        $this->validarTextoObligatorio($nombre, 'nombre');
        $this->validarTextoObligatorio($apellido, 'apellido');
        $this->validarEmail($email);
        $this->validarTextoObligatorio($contrasena, 'contrasena');
        $this->validarTextoObligatorio($rol, 'rol');
        $this->contrasena = $this->hashPassword($contrasena);
        $this->inicializarAtributosBase($id, $fechaCreacion, $fechaActualizacion);
    }

    public function nombre(): string
    {
        return $this->nombre;
    }

    public function apellido(): string
    {
        return $this->apellido;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function contrasena(): string
    {
        return $this->contrasena;
    }

    public function rol(): string
    {
        return $this->rol;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email,
            'rol' => $this->rol,
            'fecha_creacion' => $this->fechaCreacion()->format('Y-m-d H:i:s'),
            'fecha_actualizacion' => $this->fechaActualizacion()->format('Y-m-d H:i:s'),
        ];
    }

    private function validarTextoObligatorio(string $valor, string $campo): void
    {
        if (trim($valor) === '') {
            throw new \InvalidArgumentException("El campo {$campo} es obligatorio.");
        }
    }

    private function validarEmail(string $email): void
    {
        $this->validarTextoObligatorio($email, "email");
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('El email no tiene un formato válido.');
        }
    }

    private function hashPassword(string $password): string
    {
        if (password_needs_rehash($password, PASSWORD_BCRYPT)) {
            return password_hash($password, PASSWORD_BCRYPT);
        }

        return $password;
    }
}
