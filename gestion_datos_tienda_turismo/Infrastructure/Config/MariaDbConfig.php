<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\Config;

final class MariaDbConfig
{
    /** @return array<string, mixed> */
    public static function connectionParams(): array
    {
        $params = [
            'driver' => self::env('DB_DRIVER', 'pdo_mysql'),
            'dbname' => self::env('DB_DATABASE'),
            'user' => self::env('DB_USERNAME'),
            'password' => self::env('DB_PASSWORD', ''),
            'charset' => self::env('DB_CHARSET', 'utf8mb4'),
            'serverVersion' => self::env('DB_SERVER_VERSION', 'mariadb-10.11.0'),
            'driverOptions' => [
                \PDO::ATTR_TIMEOUT => (int) self::env('DB_CONNECTION_TIMEOUT', '5'),
            ],
        ];

        $unixSocket = self::env('DB_UNIX_SOCKET', '');

        if ($unixSocket !== '') {
            $params['unix_socket'] = $unixSocket;

            return $params;
        }

        $params['host'] = self::env('DB_HOST', '127.0.0.1');
        $params['port'] = (int) self::env('DB_PORT', '3306');

        return $params;
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
