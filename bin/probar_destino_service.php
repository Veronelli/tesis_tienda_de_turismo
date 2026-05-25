<?php

declare(strict_types=1);

use TiendaTurismo\GestionDatos\Application\Services\DestinoService;
use TiendaTurismo\GestionDatos\Infrastructure\Repositories\DestinoDoctrineRepository;

require dirname(__DIR__) . '/vendor/autoload.php';

$service = new DestinoService(new DestinoDoctrineRepository());
$accion = $argv[1] ?? 'listar';

try {
    $resultado = match ($accion) {
        'crear' => $service->crear([
            'ciudad' => $argv[2] ?? '',
            'estado_provincia' => $argv[3] ?? '',
            'pais' => $argv[4] ?? '',
        ]),
        'obtener' => $service->obtenerPorId((int) ($argv[2] ?? 0)),
        'listar' => $service->listar(),
        default => throw new InvalidArgumentException('Accion invalida. Use: crear, obtener o listar.'),
    };

    var_export($resultado);
    fwrite(STDOUT, PHP_EOL);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
