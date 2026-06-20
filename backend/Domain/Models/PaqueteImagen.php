<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Models;

use Doctrine\ORM\Mapping as ORM;
use TiendaTurismo\GestionDatos\Domain\Models\Traits\AtributosBase;

#[ORM\Entity]
#[ORM\Table(name: 'paquete_imagenes')]
final class PaqueteImagen
{
    use AtributosBase;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Paquete::class)]
        #[ORM\JoinColumn(name: 'paquete_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private Paquete $paquete,

        #[ORM\Column(type: 'text', columnDefinition: 'MEDIUMTEXT NOT NULL')]
        private string $imagen,

        #[ORM\Column(type: 'integer')]
        private int $orden = 0,

        ?int $id = null,
        ?\DateTimeImmutable $fechaCreacion = null,
        ?\DateTimeImmutable $fechaActualizacion = null,
    ) {
        $this->inicializarAtributosBase($id, $fechaCreacion, $fechaActualizacion);
    }

    public function paquete(): Paquete
    {
        return $this->paquete;
    }

    public function imagen(): string
    {
        return $this->imagen;
    }

    public function orden(): int
    {
        return $this->orden;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'paquete_id' => $this->paquete->id(),
            'imagen' => $this->imagen,
            'orden' => $this->orden,
            'fecha_creacion' => $this->fechaCreacion()->format('Y-m-d H:i:s'),
            'fecha_actualizacion' => $this->fechaActualizacion()->format('Y-m-d H:i:s'),
        ];
    }
}
