<?php

declare(strict_types=1);


trait AtributosBase
{
    private ?int $id;
    private \DateTimeImmutable $fechaCreacion;
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
