<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Domain\Repositories;

use TiendaTurismo\GestionDatos\Domain\Models\Consulta;

interface ConsultaRepositoryInterface
{
    public function save(Consulta $consulta): void;

    public function findById(int $id): ?Consulta;

    public function update(Consulta $consulta): void;

    public function delete(Consulta $consulta): void;

    /** @param array{estado?:string, cliente?:string, paquete_id?:int, fecha_desde?:string, fecha_hasta?:string} $filtros */
    public function findAll(array $filtros = []): array;
}
