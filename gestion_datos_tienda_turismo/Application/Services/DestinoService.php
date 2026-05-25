<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\Services;

use TiendaTurismo\GestionDatos\Application\Input\CrearDestinoInput;
use TiendaTurismo\GestionDatos\Application\UseCases\Destino\CrearDestinoUseCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Destino\ListarDestinosUseCase;
use TiendaTurismo\GestionDatos\Application\UseCases\Destino\ObtenerDestinoPorIdUseCase;
use TiendaTurismo\GestionDatos\Domain\Models\Destino;
use TiendaTurismo\GestionDatos\Domain\Repositories\DestinoRepositoryInterface;

final class DestinoService
{
    private CrearDestinoUseCase $crearDestino;
    private ObtenerDestinoPorIdUseCase $obtenerDestinoPorId;
    private ListarDestinosUseCase $listarDestinos;

    public function __construct(DestinoRepositoryInterface $destinos)
    {
        $this->crearDestino = new CrearDestinoUseCase($destinos);
        $this->obtenerDestinoPorId = new ObtenerDestinoPorIdUseCase($destinos);
        $this->listarDestinos = new ListarDestinosUseCase($destinos);
    }

    /** @param array{ciudad:string,estado_provincia?:string,estadoProvincia?:string,pais:string} $datos */
    public function crear(array $datos): array
    {
        $destino = $this->crearDestino->execute(new CrearDestinoInput(
            (string) $datos['ciudad'],
            (string) ($datos['estado_provincia'] ?? $datos['estadoProvincia'] ?? ''),
            (string) $datos['pais'],
        ));

        return $this->serializarDestino($destino);
    }

    public function obtenerPorId(int $id): ?array
    {
        $destino = $this->obtenerDestinoPorId->execute($id);

        if ($destino === null) {
            return null;
        }

        return $this->serializarDestino($destino);
    }

    /** @return list<array<string, mixed>> */
    public function listar(): array
    {
        return array_map(
            fn (Destino $destino): array => $this->serializarDestino($destino),
            $this->listarDestinos->execute(),
        );
    }

    /** @return array<string, mixed> */
    private function serializarDestino(Destino $destino): array
    {
        return $destino->toArray();
    }
}
