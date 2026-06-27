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
    public const ESTADO_PROCESANDO = 'procesando';
    public const ESTADO_CANCELADA = 'cancelada';
    public const ESTADO_COMPLETADA = 'completada';

    public const CALIFICACION_FRIO = 'Frio';
    public const CALIFICACION_CALIENTE = 'Caliente';
    public const CALIFICACION_TIBIO = 'tibio';

    private const ESTADOS_VALIDOS = [
        self::ESTADO_PENDIENTE,
        self::ESTADO_PROCESANDO,
        self::ESTADO_CANCELADA,
        self::ESTADO_COMPLETADA,
    ];

    private const CALIFICACIONES_VALIDAS = [
        self::CALIFICACION_FRIO,
        self::CALIFICACION_CALIENTE,
        self::CALIFICACION_TIBIO,
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

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $calificacion = null;

    #[ORM\Column(name: 'fecha_consulta', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $fechaConsulta;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'creado_por_usuario_id', referencedColumnName: 'id', nullable: true)]
    private ?Usuario $creadoPor = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'actualizado_por_usuario_id', referencedColumnName: 'id', nullable: true)]
    private ?Usuario $actualizadoPor = null;

    public function __construct(
        Cliente $cliente,
        Paquete $paquete,
        string $mensaje,
        ?string $calificacion = null,
        ?\DateTimeImmutable $fechaConsulta = null,
        ?Usuario $creadoPor = null,
        ?Usuario $actualizadoPor = null,
        ?int $id = null,
        ?\DateTimeImmutable $fechaCreacion = null,
        ?\DateTimeImmutable $fechaActualizacion = null,
    ) {
        $this->validarTextoObligatorio($mensaje, 'mensaje');

        $this->cliente = $cliente;
        $this->paquete = $paquete;
        $this->mensaje = $mensaje;
        $this->estado = self::ESTADO_PENDIENTE;
        $this->calificacion = $calificacion !== null ? $this->normalizarCalificacion($calificacion) : null;
        $this->fechaConsulta = $fechaConsulta ?? new \DateTimeImmutable();
        $this->creadoPor = $creadoPor;
        $this->actualizadoPor = $actualizadoPor;
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

    public function calificacion(): ?string
    {
        return $this->calificacion;
    }

    public function fechaConsulta(): ?\DateTimeImmutable
    {
        return $this->fechaConsulta;
    }

    public function creadoPor(): ?Usuario
    {
        return $this->creadoPor;
    }

    public function actualizadoPor(): ?Usuario
    {
        return $this->actualizadoPor;
    }

    public function update(
        ?Cliente $cliente = null,
        ?Paquete $paquete = null,
        ?string $mensaje = null,
        ?string $estado = null,
        ?string $calificacion = null,
        ?\DateTimeImmutable $fechaConsulta = null,
        ?Usuario $actualizadoPor = null,
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
            $estadoNormalizado = strtolower(trim($estado));
            $this->validarEstado($estadoNormalizado);
            $this->estado = $estadoNormalizado;
        }
        if ($calificacion !== null) {
            $this->calificacion = $this->normalizarCalificacion($calificacion);
        }
        if ($fechaConsulta !== null) {
            $this->fechaConsulta = $fechaConsulta;
        }

        if ($actualizadoPor !== null) {
            $this->actualizadoPor = $actualizadoPor;
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
            'calificacion' => $this->calificacion,
            'creado_por' => $this->creadoPor !== null ? [
                'id' => $this->creadoPor->id(),
                'nombre' => $this->creadoPor->nombre(),
                'apellido' => $this->creadoPor->apellido(),
                'email' => $this->creadoPor->email(),
            ] : null,
            'actualizado_por' => $this->actualizadoPor !== null ? [
                'id' => $this->actualizadoPor->id(),
                'nombre' => $this->actualizadoPor->nombre(),
                'apellido' => $this->actualizadoPor->apellido(),
                'email' => $this->actualizadoPor->email(),
            ] : null,
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

    private function normalizarCalificacion(string $calificacion): string
    {
        $valor = trim($calificacion);
        $normalizado = match (strtolower($valor)) {
            'frio' => self::CALIFICACION_FRIO,
            'caliente' => self::CALIFICACION_CALIENTE,
            'tibio' => self::CALIFICACION_TIBIO,
            default => null,
        };

        if ($normalizado === null) {
            throw new \InvalidArgumentException(
                'Calificación inválida. Valores permitidos: ' . implode(', ', self::CALIFICACIONES_VALIDAS)
            );
        }

        return $normalizado;
    }
}
