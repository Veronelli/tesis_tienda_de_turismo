<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'paquetes')]
final class Paquete
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'fecha_creacion', type: 'datetime_immutable', columnDefinition: 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP')]
    private \DateTimeImmutable $fechaCreacion;

    #[ORM\Column(name: 'fecha_actualizacion', type: 'datetime_immutable', columnDefinition: 'DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP', nullable: true)]
    private ?\DateTimeImmutable $fechaActualizacion = null;

    #[ORM\Column(name: 'nombre_paquete', type: 'string', length: 150)]
    private string $nombrePaquete;

    #[ORM\Column(type: 'text')]
    private string $descripcion;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $precio;

    #[ORM\Column(name: 'imagen_primaria', type: 'text', columnDefinition: 'MEDIUMTEXT NOT NULL')]
    private string $imagenPrimaria;

    #[ORM\Column(name: 'imagen_secundaria', type: 'text', columnDefinition: 'MEDIUMTEXT DEFAULT NULL', nullable: true)]
    private ?string $imagenSecundaria = null;

    #[ORM\Column(name: 'fecha_partida', type: 'date_immutable')]
    private \DateTimeImmutable $fechaPartida;

    #[ORM\Column(name: 'fecha_vuelta', type: 'date_immutable')]
    private \DateTimeImmutable $fechaVuelta;

    #[ORM\Column(name: 'incluye_desayuno', type: 'boolean')]
    private bool $incluyeDesayuno;

    #[ORM\Column(name: 'all_inclusive', type: 'boolean')]
    private bool $allInclusive;

    #[ORM\Column(type: 'boolean')]
    private bool $pileta;

    #[ORM\Column(type: 'boolean')]
    private bool $publico;

    #[ORM\Column(type: 'integer')]
    private int $cantidad;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'creado_por', referencedColumnName: 'id', nullable: false)]
    private Usuario $creadoPor;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'actualizado_por', referencedColumnName: 'id', nullable: false)]
    private Usuario $actualizadoPor;

    /** @var Collection<int, PaquetesHoteles> */
    #[ORM\OneToMany(targetEntity: PaquetesHoteles::class, mappedBy: 'paquete', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $paquetesHoteles;

    public function __construct(
        string $nombrePaquete,
        string $descripcion,
        string $precio,
        string $imagenPrimaria,
        \DateTimeImmutable $fechaPartida,
        \DateTimeImmutable $fechaVuelta,
        bool $incluyeDesayuno,
        bool $allInclusive,
        bool $pileta,
        bool $publico,
        int $cantidad,
        Usuario $creadoPor,
        Usuario $actualizadoPor,
        ?string $imagenSecundaria = null,
        ?int $id = null,
        ?\DateTimeImmutable $fechaCreacion = null,
        ?\DateTimeImmutable $fechaActualizacion = null,
    ) {
        $this->validarTextoObligatorio($nombrePaquete, 'nombre_paquete');
        $this->validarTextoObligatorio($descripcion, 'descripcion');
        $this->validarPrecio($precio);

        $this->nombrePaquete = $nombrePaquete;
        $this->descripcion = $descripcion;
        $this->precio = $precio;
        $this->imagenPrimaria = $imagenPrimaria;
        $this->imagenSecundaria = $imagenSecundaria;
        $this->fechaPartida = $fechaPartida;
        $this->fechaVuelta = $fechaVuelta;
        $this->incluyeDesayuno = $incluyeDesayuno;
        $this->allInclusive = $allInclusive;
        $this->pileta = $pileta;
        $this->publico = $publico;
        $this->cantidad = $cantidad;
        $this->creadoPor = $creadoPor;
        $this->actualizadoPor = $actualizadoPor;
        $this->paquetesHoteles = new ArrayCollection();

        $ahora = new \DateTimeImmutable();
        $this->id = $id;
        $this->fechaCreacion = $fechaCreacion ?? $ahora;
        $this->fechaActualizacion = $fechaActualizacion;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function fechaCreacion(): \DateTimeImmutable
    {
        return $this->fechaCreacion;
    }

    public function fechaActualizacion(): ?\DateTimeImmutable
    {
        return $this->fechaActualizacion;
    }

    public function nombrePaquete(): string
    {
        return $this->nombrePaquete;
    }

    public function descripcion(): string
    {
        return $this->descripcion;
    }

    public function precio(): string
    {
        return $this->precio;
    }

    public function imagenPrimaria(): string
    {
        return $this->imagenPrimaria;
    }

    public function imagenSecundaria(): ?string
    {
        return $this->imagenSecundaria;
    }

    public function fechaPartida(): \DateTimeImmutable
    {
        return $this->fechaPartida;
    }

    public function fechaVuelta(): \DateTimeImmutable
    {
        return $this->fechaVuelta;
    }

    public function incluyeDesayuno(): bool
    {
        return $this->incluyeDesayuno;
    }

    public function allInclusive(): bool
    {
        return $this->allInclusive;
    }

    public function pileta(): bool
    {
        return $this->pileta;
    }

    public function publico(): bool
    {
        return $this->publico;
    }

    public function cantidad(): int
    {
        return $this->cantidad;
    }

    public function creadoPor(): Usuario
    {
        return $this->creadoPor;
    }

    public function actualizadoPor(): Usuario
    {
        return $this->actualizadoPor;
    }

    /** @return Collection<int, Hotel> */
    public function hoteles(): Collection
    {
        return $this->paquetesHoteles->map(fn (PaquetesHoteles $ph) => $ph->hotel());
    }

    /** @return Collection<int, PaquetesHoteles> */
    public function paquetesHoteles(): Collection
    {
        return $this->paquetesHoteles;
    }

    public function update(
        string $nombrePaquete,
        string $descripcion,
        string $precio,
        string $imagenPrimaria,
        \DateTimeImmutable $fechaPartida,
        \DateTimeImmutable $fechaVuelta,
        bool $incluyeDesayuno,
        bool $allInclusive,
        bool $pileta,
        bool $publico,
        int $cantidad,
        Usuario $actualizadoPor,
        ?string $imagenSecundaria = null,
    ): void {
        $this->validarTextoObligatorio($nombrePaquete, 'nombre_paquete');
        $this->validarTextoObligatorio($descripcion, 'descripcion');
        $this->validarPrecio($precio);

        $this->nombrePaquete = $nombrePaquete;
        $this->descripcion = $descripcion;
        $this->precio = $precio;
        $this->imagenPrimaria = $imagenPrimaria;
        $this->imagenSecundaria = $imagenSecundaria;
        $this->fechaPartida = $fechaPartida;
        $this->fechaVuelta = $fechaVuelta;
        $this->incluyeDesayuno = $incluyeDesayuno;
        $this->allInclusive = $allInclusive;
        $this->pileta = $pileta;
        $this->publico = $publico;
        $this->cantidad = $cantidad;
        $this->actualizadoPor = $actualizadoPor;
    }

    /** @param list<Hotel> $hoteles */
    public function syncHoteles(array $hoteles): void
    {
        $this->paquetesHoteles->clear();
        foreach ($hoteles as $hotel) {
            $this->paquetesHoteles->add(new PaquetesHoteles($this, $hotel));
        }
    }

    public function toArray(): array
    {
        $hotelesArray = [];
        foreach ($this->paquetesHoteles as $ph) {
            $hotel = $ph->hotel();
            $hotelesArray[] = [
                'id' => $hotel->id(),
                'nombre' => $hotel->nombre(),
                'ubicacion' => $hotel->ubicacion(),
                'destino' => $hotel->destino()->toArray(),
            ];
        }

        return [
            'id' => $this->id,
            'nombre_paquete' => $this->nombrePaquete,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'imagen_primaria' => $this->imagenPrimaria,
            'imagen_secundaria' => $this->imagenSecundaria,
            'fecha_partida' => $this->fechaPartida->format('Y-m-d'),
            'fecha_vuelta' => $this->fechaVuelta->format('Y-m-d'),
            'incluye_desayuno' => $this->incluyeDesayuno,
            'all_inclusive' => $this->allInclusive,
            'pileta' => $this->pileta,
            'publico' => $this->publico,
            'cantidad' => $this->cantidad,
            'creado_por' => [
                'id' => $this->creadoPor->id(),
                'nombre' => $this->creadoPor->nombre(),
                'apellido' => $this->creadoPor->apellido(),
                'email' => $this->creadoPor->email(),
            ],
            'actualizado_por' => [
                'id' => $this->actualizadoPor->id(),
                'nombre' => $this->actualizadoPor->nombre(),
                'apellido' => $this->actualizadoPor->apellido(),
                'email' => $this->actualizadoPor->email(),
            ],
            'hoteles' => $hotelesArray,
            'fecha_creacion' => $this->fechaCreacion->format('Y-m-d H:i:s'),
            'fecha_actualizacion' => $this->fechaActualizacion?->format('Y-m-d H:i:s'),
        ];
    }

    private function validarTextoObligatorio(string $valor, string $campo): void
    {
        if (trim($valor) === '') {
            throw new \InvalidArgumentException("El campo {$campo} es obligatorio.");
        }

        if ($campo === 'nombre_paquete' && mb_strlen($valor) > 150) {
            throw new \InvalidArgumentException("El campo {$campo} no puede superar 150 caracteres.");
        }
    }

    private function validarPrecio(string $precio): void
    {
        if (!is_numeric($precio) || (float) $precio < 0) {
            throw new \InvalidArgumentException('El precio debe ser un valor numérico válido y no negativo.');
        }
    }
}
