<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use TiendaTurismo\GestionDatos\Domain\Models\Traits\AtributosBase;

#[ORM\Entity]
#[ORM\Table(name: 'paquetes')]
final class Paquete
{
    use AtributosBase;

    #[ORM\Column(type: 'string', length: 200)]
    private string $nombre;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $descripcion;

    #[ORM\Column(name: 'fecha_partida', type: 'date_immutable')]
    private \DateTimeImmutable $fechaPartida;

    #[ORM\Column(name: 'fecha_vuelta', type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $fechaVuelta;

    #[ORM\Column(type: 'decimal', precision: 11, scale: 2)]
    private string $precio;

    #[ORM\Column(type: 'boolean')]
    private bool $disponible;

    #[ORM\Column(name: 'imagen_principal', type: 'text', nullable: true, columnDefinition: 'MEDIUMTEXT NULL')]
    private ?string $imagenPrincipal = null;

    #[ORM\Column(name: 'imagen_secundaria', type: 'text', nullable: true, columnDefinition: 'MEDIUMTEXT NULL')]
    private ?string $imagenSecundaria = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'creado_por_usuario_id', referencedColumnName: 'id', nullable: false)]
    private Usuario $creadoPor;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'actualizado_por_usuario_id', referencedColumnName: 'id', nullable: true)]
    private ?Usuario $actualizadoPor;

    /** @var Collection<int, Hotel> */
    #[ORM\ManyToMany(targetEntity: Hotel::class)]
    #[ORM\JoinTable(name: 'paquete_hotel')]
    private Collection $hoteles;

    public function __construct(
        string $nombre,
        ?string $descripcion,
        \DateTimeImmutable $fechaPartida,
        ?\DateTimeImmutable $fechaVuelta,
        string $precio,
        bool $disponible,
        Usuario $creadoPor,
        ?string $imagenPrincipal = null,
        ?string $imagenSecundaria = null,
        ?int $id = null,
        ?\DateTimeImmutable $fechaCreacion = null,
        ?\DateTimeImmutable $fechaActualizacion = null,
    ) {
        $this->validarTextoObligatorio($nombre, 'nombre');
        $this->validarPrecio($precio);

        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->fechaPartida = $fechaPartida;
        $this->fechaVuelta = $fechaVuelta;
        $this->precio = $precio;
        $this->disponible = $disponible;
        $this->imagenPrincipal = $imagenPrincipal;
        $this->imagenSecundaria = $imagenSecundaria;
        $this->creadoPor = $creadoPor;
        $this->actualizadoPor = null;
        $this->hoteles = new ArrayCollection();
        $this->inicializarAtributosBase($id, $fechaCreacion, $fechaActualizacion);
    }

    public function nombre(): string
    {
        return $this->nombre;
    }

    public function descripcion(): ?string
    {
        return $this->descripcion;
    }

    public function fechaPartida(): \DateTimeImmutable
    {
        return $this->fechaPartida;
    }

    public function fechaVuelta(): ?\DateTimeImmutable
    {
        return $this->fechaVuelta;
    }

    public function precio(): string
    {
        return $this->precio;
    }

    public function disponible(): bool
    {
        return $this->disponible;
    }

    public function imagenPrincipal(): ?string
    {
        return $this->imagenPrincipal;
    }

    public function imagenSecundaria(): ?string
    {
        return $this->imagenSecundaria;
    }

    public function creadoPor(): Usuario
    {
        return $this->creadoPor;
    }

    public function actualizadoPor(): ?Usuario
    {
        return $this->actualizadoPor;
    }

    /** @return Collection<int, Hotel> */
    public function hoteles(): Collection
    {
        return $this->hoteles;
    }

    public function update(
        string $nombre,
        ?string $descripcion,
        \DateTimeImmutable $fechaPartida,
        ?\DateTimeImmutable $fechaVuelta,
        string $precio,
        bool $disponible,
        Usuario $actualizadoPor,
        ?string $imagenPrincipal = null,
        ?string $imagenSecundaria = null,
    ): void {
        $this->validarTextoObligatorio($nombre, 'nombre');
        $this->validarPrecio($precio);

        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->fechaPartida = $fechaPartida;
        $this->fechaVuelta = $fechaVuelta;
        $this->precio = $precio;
        $this->disponible = $disponible;
        $this->imagenPrincipal = $imagenPrincipal;
        if ($imagenSecundaria !== null) {
            $this->imagenSecundaria = $imagenSecundaria;
        }
        $this->actualizadoPor = $actualizadoPor;
    }

    /** @param list<Hotel> $hoteles */
    public function syncHoteles(array $hoteles): void
    {
        $this->hoteles->clear();
        foreach ($hoteles as $hotel) {
            $this->hoteles->add($hotel);
        }
    }

    public function toArray(): array
    {
        $destinosArray = [];
        $destinoNombres = [];
        $hotelesArray = [];
        foreach ($this->hoteles as $hotel) {
            $destino = $hotel->destino();
            $key = $destino->id();
            $destinosArray[$key] = $destino->toArray();

            $hotelesArray[] = [
                'id' => $hotel->id(),
                'nombre' => $hotel->nombre(),
                'ubicacion' => $hotel->ubicacion(),
                'ciudad' => $destino->ciudad(),
                'provincia' => $destino->estadoProvincia(),
                'pais' => $destino->pais(),
                'destino' => $destino->toArray(),
            ];

            $partesDestino = array_filter([$destino->ciudad(), $destino->estadoProvincia(), $destino->pais()]);
            $destinoNombres[] = implode(', ', $partesDestino);
        }

        $destinoNombres = array_unique($destinoNombres);

        return [
            'id' => $this->id(),
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'imagen_principal' => $this->imagenPrincipal,
            'imagen_secundaria' => $this->imagenSecundaria,
            'fecha_partida' => $this->fechaPartida->format('Y-m-d'),
            'fecha_vuelta' => $this->fechaVuelta?->format('Y-m-d'),
            'precio' => $this->precio,
            'disponible' => $this->disponible,
            'creado_por' => [
                'id' => $this->creadoPor->id(),
                'nombre' => $this->creadoPor->nombre(),
                'apellido' => $this->creadoPor->apellido(),
                'email' => $this->creadoPor->email(),
            ],
            'actualizado_por' => $this->actualizadoPor !== null ? [
                'id' => $this->actualizadoPor->id(),
                'nombre' => $this->actualizadoPor->nombre(),
                'apellido' => $this->actualizadoPor->apellido(),
                'email' => $this->actualizadoPor->email(),
            ] : null,
            'destinos' => array_values($destinosArray),
            'hoteles' => $hotelesArray,
            'destino_nombre' => implode(' / ', $destinoNombres),
            'fecha_creacion' => $this->fechaCreacion()->format('Y-m-d H:i:s'),
            'fecha_actualizacion' => $this->fechaActualizacion()->format('Y-m-d H:i:s'),
        ];
    }

    private function validarTextoObligatorio(string $valor, string $campo): void
    {
        if (trim($valor) === '') {
            throw new \InvalidArgumentException("El campo {$campo} es obligatorio.");
        }

        if (mb_strlen($valor) > 200) {
            throw new \InvalidArgumentException("El campo {$campo} no puede superar 200 caracteres.");
        }
    }

    private function validarPrecio(string $precio): void
    {
        if (!is_numeric($precio) || (float) $precio < 0) {
            throw new \InvalidArgumentException('El precio debe ser un valor numérico válido y no negativo.');
        }
    }
}
