<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\Config;

final class EnvLoader
{
    public static function load(string $path): void
    {
        if (! is_file($path)) {
            throw new \RuntimeException("No existe el archivo de configuracion: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            throw new \RuntimeException("No se pudo leer el archivo de configuracion: {$path}");
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            $value = trim($value, '"\'');

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}
