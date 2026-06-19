<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Models\Traits;

use Doctrine\ORM\Mapping as ORM;

trait AtributosBase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(name: 'fecha_creacion', type: 'datetime_immutable', columnDefinition: 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP')]
    private \DateTimeImmutable $fechaCreacion;

    #[ORM\Column(name: 'fecha_actualizacion', type: 'datetime_immutable', columnDefinition: 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')]
    private \DateTimeImmutable $fechaActualizacion;

    public function id(): ?int
    {
        return $this->id;
    }

    public function fechaCreacion(): \DateTimeImmutable
    {
        return $this->fechaCreacion;
    }

    public function fechaActualizacion(): \DateTimeImmutable
    {
        return $this->fechaActualizacion;
    }

    private function inicializarAtributosBase(
        ?int $id,
        ?\DateTimeImmutable $fechaCreacion,
        ?\DateTimeImmutable $fechaActualizacion,
    ): void {
        $ahora = new \DateTimeImmutable();

        $this->id = $id;
        $this->fechaCreacion = $fechaCreacion ?? $ahora;
        $this->fechaActualizacion = $fechaActualizacion ?? $ahora;
    }
}
