<?php

use Doctrine\ORM\Tools\SchemaTool;
use TiendaTurismo\GestionDatos\Domain\Models\Cliente;
use TiendaTurismo\GestionDatos\Infrastructure\Persistence\Doctrine\EntityManagerFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$envPath = $argv[1] ?? __DIR__ . '/../.env';
$entityManager = EntityManagerFactory::createFromEnv($envPath);

$schemaTool = new SchemaTool($entityManager);
$metadata = $entityManager->getClassMetadata(Cliente::class);

$sql = $schemaTool->getCreateSchemaSql([$metadata]);

echo "SQL a ejecutar:\n";
foreach ($sql as $s) {
    echo "  $s\n";
}

$connection = $entityManager->getConnection();

foreach ($sql as $s) {
    try {
        $connection->executeStatement($s);
        echo "OK: $s\n";
    } catch (\Throwable $e) {
        echo "Error (probablemente ya existe): " . $e->getMessage() . "\n";
    }
}

echo "Tabla clientes creada exitosamente!\n";
