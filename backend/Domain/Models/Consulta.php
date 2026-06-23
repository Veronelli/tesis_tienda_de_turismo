<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Models;

use Doctrine\ORM\Mapping as ORM;
use TiendaTurismo\GestionDatos\Domain\Models\Traits\AtributosBase;

#[ORM\Entity]
#[ORM\Table(name: 'consultas')]
final class Consulta
{
    use AtributosBase;

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_RESPONDIDA = 'respondida';
    public const ESTADO_CERRADA = 'cerrada';

    private const ESTADOS_VALIDOS = [
        self::ESTADO_PENDIENTE,
        self::ESTADO_RESPONDIDA,
        self::ESTADO_CERRADA,
    ];

    #[ORM\ManyToOne(targetEntity: Cliente::class)]
    #[ORM\JoinColumn(name: 'cliente_id', referencedColumnName: 'id', nullable: false)]
    private Cliente $cliente;

    #[ORM\ManyToOne(targetEntity: Paquete::class)]
    #[ORM\JoinColumn(name: 'paquete_id', referencedColumnName: 'id', nullable: false)]
    private Paquete $paquete;

    #[ORM\Column(type: 'text')]
    private string $mensaje;

    #[ORM\Column(type: 'string', length: 20)]
    private string $estado;

    #[ORM\Column(name: 'fecha_consulta', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $fechaConsulta;

    public function __construct(
        Cliente $cliente,
        Paquete $paquete,
        string $mensaje,
        ?\DateTimeImmutable $fechaConsulta = null,
        ?int $id = null,
        ?\DateTimeImmutable $fechaCreacion = null,
        ?\DateTimeImmutable $fechaActualizacion = null,
    ) {
        $this->validarTextoObligatorio($mensaje, 'mensaje');

        $this->cliente = $cliente;
        $this->paquete = $paquete;
        $this->mensaje = $mensaje;
        $this->estado = self::ESTADO_PENDIENTE;
        $this->fechaConsulta = $fechaConsulta ?? new \DateTimeImmutable();
        $this->inicializarAtributosBase($id, $fechaCreacion, $fechaActualizacion);
    }

    public function cliente(): Cliente
    {
        return $this->cliente;
    }

    public function paquete(): Paquete
    {
        return $this->paquete;
    }

    public function mensaje(): string
    {
        return $this->mensaje;
    }

    public function estado(): string
    {
        return $this->estado;
    }

    public function fechaConsulta(): ?\DateTimeImmutable
    {
        return $this->fechaConsulta;
    }

    public function update(
        ?Cliente $cliente = null,
        ?Paquete $paquete = null,
        ?string $mensaje = null,
        ?string $estado = null,
        ?\DateTimeImmutable $fechaConsulta = null,
    ): void {
        if ($cliente !== null) {
            $this->cliente = $cliente;
        }
        if ($paquete !== null) {
            $this->paquete = $paquete;
        }
        if ($mensaje !== null) {
            $this->validarTextoObligatorio($mensaje, 'mensaje');
            $this->mensaje = $mensaje;
        }
        if ($estado !== null) {
            $this->validarEstado($estado);
            $this->estado = $estado;
        }
        if ($fechaConsulta !== null) {
            $this->fechaConsulta = $fechaConsulta;
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'cliente' => [
                'id' => $this->cliente->id(),
                'nombre' => $this->cliente->nombre(),
                'apellido' => $this->cliente->apellido(),
                'email' => $this->cliente->email(),
                'telefono' => $this->cliente->telefono(),
                'dni' => $this->cliente->dni(),
                'ubicacion' => $this->cliente->ubicacion(),
            ],
            'paquete' => [
                'id' => $this->paquete->id(),
                'nombre' => $this->paquete->nombre(),
                'fecha_partida' => $this->paquete->fechaPartida()->format('Y-m-d'),
                'fecha_vuelta' => $this->paquete->fechaVuelta()?->format('Y-m-d'),
            ],
            'mensaje' => $this->mensaje,
            'estado' => $this->estado,
            'fecha_consulta' => $this->fechaConsulta?->format('Y-m-d H:i:s'),
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

    private function validarEstado(string $estado): void
    {
        if (!in_array($estado, self::ESTADOS_VALIDOS, true)) {
            throw new \InvalidArgumentException(
                "Estado inválido. Valores permitidos: " . implode(', ', self::ESTADOS_VALIDOS)
            );
        }
    }
}
