<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\Config;

final class MariaDbConfig
{
    /** @return array<string, mixed> */
    public static function connectionParams(): array
    {
        return [
            'driver' => self::env('DB_DRIVER', 'pdo_mysql'),
            'host' => self::env('DB_HOST', '127.0.0.1'),
            'port' => (int) self::env('DB_PORT', '3306'),
            'dbname' => self::env('DB_DATABASE'),
            'user' => self::env('DB_USERNAME'),
            'password' => self::env('DB_PASSWORD', ''),
            'charset' => self::env('DB_CHARSET', 'utf8mb4'),
        ];
    }

    public static function destinosTable(): string
    {
        return self::env('DB_TABLE_DESTINOS', 'destinos');
    }

    private static function env(string $key, ?string $default = null): string
    {
        $value = getenv($key);

        if ($value !== false) {
            return $value;
        }

        if (array_key_exists($key, $_ENV)) {
            return (string) $_ENV[$key];
        }

        if ($default !== null) {
            return $default;
        }

        throw new \RuntimeException("Falta configurar la variable de entorno {$key}.");
    }
}
