<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Models;

use Doctrine\ORM\Mapping as ORM;
use TiendaTurismo\GestionDatos\Domain\Models\Traits\AtributosBase;

#[ORM\Entity]
#[ORM\Table(name: 'hoteles')]
final class Hotel
{
    use AtributosBase;

    public function __construct(
        #[ORM\Column(type: 'string', length: 200)]
        private string $nombre,
        #[ORM\Column(type: 'string', length: 255)]
        private string $ubicacion,
        #[ORM\ManyToOne(targetEntity: Destino::class, fetch: 'EAGER')]
        #[ORM\JoinColumn(name: 'destino_id', referencedColumnName: 'id', nullable: false)]
        private Destino $destino,
        ?int $id = null,
        ?\DateTimeImmutable $fechaCreacion = null,
        ?\DateTimeImmutable $fechaActualizacion = null,
    ) {
        $this->validarTextoObligatorio($nombre, 'nombre');
        $this->validarTextoObligatorio($ubicacion, 'ubicacion');
        $this->inicializarAtributosBase($id, $fechaCreacion, $fechaActualizacion);
    }

    public function nombre(): string
    {
        return $this->nombre;
    }

    public function ubicacion(): string
    {
        return $this->ubicacion;
    }

    public function destino(): Destino
    {
        return $this->destino;
    }

    public function update(string $nombre, string $ubicacion, Destino $destino): void
    {
        $this->validarTextoObligatorio($nombre, 'nombre');
        $this->validarTextoObligatorio($ubicacion, 'ubicacion');
        $this->nombre = $nombre;
        $this->ubicacion = $ubicacion;
        $this->destino = $destino;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'nombre' => $this->nombre,
            'ubicacion' => $this->ubicacion,
            'destino_id' => $this->destino->id(),
            'destino' => $this->destino->toArray(),
            'fecha_creacion' => $this->fechaCreacion()->format('Y-m-d H:i:s'),
            'fecha_actualizacion' => $this->fechaActualizacion()->format('Y-m-d H:i:s'),
        ];
    }

    private function validarTextoObligatorio(string $valor, string $campo): void
    {
        if (trim($valor) === '') {
            throw new \InvalidArgumentException("El campo {$campo} es obligatorio.");
        }

        if ($campo === 'nombre' && mb_strlen($valor) > 200) {
            throw new \InvalidArgumentException("El campo {$campo} no puede superar 200 caracteres.");
        }

        if ($campo === 'ubicacion' && mb_strlen($valor) > 255) {
            throw new \InvalidArgumentException("El campo {$campo} no puede superar 255 caracteres.");
        }
    }
}
