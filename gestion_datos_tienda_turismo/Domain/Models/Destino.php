<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Models;

use Doctrine\ORM\Mapping as ORM;
use TiendaTurismo\GestionDatos\Domain\Models\Traits\AtributosBase;

#[ORM\Entity]
#[ORM\Table(name: 'destinos')]
final class Destino
{
    use AtributosBase;

    public function __construct(
        #[ORM\Column(type: 'string', length: 150)]
        private string $ciudad,
        #[ORM\Column(name: 'estado_provincia', type: 'string', length: 150)]
        private string $estadoProvincia,
        #[ORM\Column(type: 'string', length: 150)]
        private string $pais,
        ?int $id = null,
        ?\DateTimeImmutable $fechaCreacion = null,
        ?\DateTimeImmutable $fechaActualizacion = null,
    ) {
        $this->validarTextoObligatorio($ciudad, 'ciudad');
        $this->validarTextoObligatorio($estadoProvincia, 'estado_provincia');
        $this->validarTextoObligatorio($pais, 'pais');
        $this->inicializarAtributosBase($id, $fechaCreacion, $fechaActualizacion);
    }

    public function ciudad(): string
    {
        return $this->ciudad;
    }

    public function estadoProvincia(): string
    {
        return $this->estadoProvincia;
    }

    public function pais(): string
    {
        return $this->pais;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'ciudad' => $this->ciudad,
            'estado_provincia' => $this->estadoProvincia,
            'pais' => $this->pais,
            'fecha_creacion' => $this->fechaCreacion()->format('Y-m-d H:i:s'),
            'fecha_actualizacion' => $this->fechaActualizacion()->format('Y-m-d H:i:s'),
        ];
    }

    private function validarTextoObligatorio(string $valor, string $campo): void
    {
        if (trim($valor) === '') {
            throw new \InvalidArgumentException("El campo {$campo} es obligatorio.");
        }

        if (mb_strlen($valor) > 150) {
            throw new \InvalidArgumentException("El campo {$campo} no puede superar 150 caracteres.");
        }
    }
}
