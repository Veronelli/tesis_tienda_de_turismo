<?php

declare(strict_types=1);

use Doctrine\ORM\Tools\SchemaTool;
use TiendaTurismo\GestionDatos\Infrastructure\Persistence\Doctrine\EntityManagerFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$envPath = $argv[1] ?? __DIR__ . '/../.env';

if (!file_exists($envPath)) {
    echo "Error: No existe el archivo de configuración: {$envPath}\n";
    echo "Uso: php bin/crear_tablas.php [ruta/.env]\n";
    exit(1);
}

$entityManager = EntityManagerFactory::createFromEnv($envPath);
$metadata = $entityManager->getMetadataFactory()->getAllMetadata();

if (empty($metadata)) {
    echo "No se encontraron entidades mapeadas.\n";
    exit(1);
}

echo "Entidades encontradas:\n";
foreach ($metadata as $class) {
    echo "  - {$class->getName()} (tabla: {$class->getTableName()})\n";
}

$schemaTool = new SchemaTool($entityManager);
$connection = $entityManager->getConnection();
$schemaManager = method_exists($connection, 'createSchemaManager')
    ? $connection->createSchemaManager()
    : $connection->getSchemaManager();

echo "\nCreando tablas...\n";
foreach ($metadata as $class) {
    $tableName = $class->getTableName();

    if ($schemaManager->tablesExist([$tableName])) {
        echo "  - {$tableName} ya existe, se omite.\n";
        continue;
    }

    $sql = $schemaTool->getCreateSchemaSql([$class]);

    echo "  - Creando {$tableName}\n";
    foreach ($sql as $stmt) {
        try {
            $connection->executeStatement($stmt);
            echo "    OK: {$stmt}\n";
        } catch (Throwable $e) {
            echo "    Error en {$tableName}: " . $e->getMessage() . "\n";
            break;
        }
    }
}

echo "Proceso finalizado.\n";
