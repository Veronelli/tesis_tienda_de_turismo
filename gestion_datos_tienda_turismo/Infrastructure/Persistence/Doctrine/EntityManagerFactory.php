<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use TiendaTurismo\GestionDatos\Infrastructure\Config\EnvLoader;
use TiendaTurismo\GestionDatos\Infrastructure\Config\MariaDbConfig;

final class EntityManagerFactory
{
    public static function createFromEnv(?string $envPath = null, bool $isDevMode = true): EntityManagerInterface
    {
        EnvLoader::load($envPath ?? dirname(__DIR__, 4) . '/.env');

        return self::create(MariaDbConfig::connectionParams(), $isDevMode);
    }

    /** @param array<string, mixed> $connectionParams */
    public static function create(array $connectionParams, bool $isDevMode = true): EntityManagerInterface
    {
        $rootPath = dirname(__DIR__, 4);
        $config = ORMSetup::createAttributeMetadataConfig([
            dirname(__DIR__, 3) . '/Domain/Models',
        ], $isDevMode);
        $config->setProxyDir($rootPath . '/var/doctrine/proxies');
        $config->setProxyNamespace('TiendaTurismo\\GestionDatos\\Infrastructure\\Persistence\\Doctrine\\Proxies');

        $connection = DriverManager::getConnection($connectionParams, $config);

        return new EntityManager($connection, $config);
    }
}
