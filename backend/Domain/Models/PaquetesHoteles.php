<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Models;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'paquetes_hoteles')]
final class PaquetesHoteles
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Paquete::class, inversedBy: 'paquetesHoteles')]
    #[ORM\JoinColumn(name: 'paquete_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Paquete $paquete;

    #[ORM\ManyToOne(targetEntity: Hotel::class, inversedBy: 'paquetesHoteles')]
    #[ORM\JoinColumn(name: 'hotel_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Hotel $hotel;

    public function __construct(Paquete $paquete, Hotel $hotel, ?int $id = null)
    {
        $this->paquete = $paquete;
        $this->hotel = $hotel;
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function paquete(): Paquete
    {
        return $this->paquete;
    }

    public function hotel(): Hotel
    {
        return $this->hotel;
    }
}
