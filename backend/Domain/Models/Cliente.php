<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Models;

use Doctrine\ORM\Mapping as ORM;
use TiendaTurismo\GestionDatos\Domain\Models\Traits\AtributosBase;

#[ORM\Entity]
#[ORM\Table(name: 'clientes')]
final class Cliente
{
    use AtributosBase;

    public function __construct(
        #[ORM\Column(type: 'string', length: 100)]
        private string $nombre,
        #[ORM\Column(type: 'string', length: 100)]
        private string $apellido,
        #[ORM\Column(type: 'string', length: 255)]
        private string $email,
        #[ORM\Column(type: 'string', length: 20)]
        private string $telefono,
        #[ORM\Column(type: 'string', length: 20)]
        private string $dni,
        #[ORM\Column(type: 'string', length: 255)]
        private string $ubicacion,
        ?int $id = null,
        ?\DateTimeImmutable $fechaCreacion = null,
        ?\DateTimeImmutable $fechaActualizacion = null,
    ) {
        $this->validarTextoObligatorio($nombre, 'nombre');
        $this->validarTextoObligatorio($apellido, 'apellido');
        $this->validarEmail($email);
        $this->validarTextoObligatorio($telefono, 'telefono');
        $this->validarTextoObligatorio($dni, 'dni');
        $this->validarTextoObligatorio($ubicacion, 'ubicacion');
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

    public function telefono(): string
    {
        return $this->telefono;
    }

    public function dni(): string
    {
        return $this->dni;
    }

    public function ubicacion(): string
    {
        return $this->ubicacion;
    }

    public function update(string $nombre, string $apellido, string $email, string $telefono, string $dni, string $ubicacion): void
    {
        $this->validarTextoObligatorio($nombre, 'nombre');
        $this->validarTextoObligatorio($apellido, 'apellido');
        $this->validarEmail($email);
        $this->validarTextoObligatorio($telefono, 'telefono');
        $this->validarTextoObligatorio($dni, 'dni');
        $this->validarTextoObligatorio($ubicacion, 'ubicacion');
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->email = $email;
        $this->telefono = $telefono;
        $this->dni = $dni;
        $this->ubicacion = $ubicacion;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'dni' => $this->dni,
            'ubicacion' => $this->ubicacion,
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
        if (trim($email) === '') {
            throw new \InvalidArgumentException('El campo email es obligatorio.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('El formato del email no es válido.');
        }
    }
}
