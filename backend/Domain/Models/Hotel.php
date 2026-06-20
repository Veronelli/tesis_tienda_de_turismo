<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use TiendaTurismo\GestionDatos\Domain\Models\Traits\AtributosBase;

#[ORM\Entity]
#[ORM\Table(name: 'hoteles')]
class Hotel
{
    use AtributosBase;

    /** @var Collection<int, PaquetesHoteles> */
    #[ORM\OneToMany(targetEntity: PaquetesHoteles::class, mappedBy: 'hotel')]
    private Collection $paquetesHoteles;

    public function __construct(
        #[ORM\Column(type: 'string', length: 150)]
        private string $nombre,

        #[ORM\Column(type: 'string', length: 150)]
        private string $ubicacion,

        #[ORM\Column(type: 'text')]
        private string $descripcion,

        #[ORM\ManyToOne(targetEntity: Destino::class, fetch: 'EAGER')]
        #[ORM\JoinColumn(name: 'destino_id', referencedColumnName: 'id', nullable: false)]
        private Destino $destino,

        ?int $id = null,
        ?\DateTimeImmutable $fechaCreacion = null,
        ?\DateTimeImmutable $fechaActualizacion = null,
    ) {
        $this->validarTextoObligatorio($nombre, 'nombre');
        $this->validarTextoObligatorio($ubicacion, 'ubicacion');
        $this->validarTextoObligatorio($descripcion, 'descripcion');
        $this->paquetesHoteles = new ArrayCollection();
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

    public function descripcion(): string
    {
        return $this->descripcion;
    }

    public function destino(): Destino
    {
        return $this->destino;
    }

    /** @return Collection<int, PaquetesHoteles> */
    public function paquetesHoteles(): Collection
    {
        return $this->paquetesHoteles;
    }

    public function update(string $nombre, string $ubicacion, string $descripcion, Destino $destino): void
    {
        $this->validarTextoObligatorio($nombre, 'nombre');
        $this->validarTextoObligatorio($ubicacion, 'ubicacion');
        $this->validarTextoObligatorio($descripcion, 'descripcion');
        $this->nombre = $nombre;
        $this->ubicacion = $ubicacion;
        $this->descripcion = $descripcion;
        $this->destino = $destino;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'nombre' => $this->nombre,
            'ubicacion' => $this->ubicacion,
            'descripcion' => $this->descripcion,
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

        if (in_array($campo, ['nombre', 'ubicacion'], true) && mb_strlen($valor) > 150) {
            throw new \InvalidArgumentException("El campo {$campo} no puede superar 150 caracteres.");
        }
    }
}
