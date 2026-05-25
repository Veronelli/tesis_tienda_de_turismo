# Gestion de Datos - Tienda de Turismo

Modulo PHP preparado con arquitectura hexagonal para aislar la logica de negocio de los detalles externos.

## Estructura

- `Domain/Models`: modelos del dominio definidos por requerimiento explicito.
- `Domain/Repositories`: interfaces de repositorios que necesita el dominio.
- `Application/Input`: datos de entrada para los casos de uso.
- `Application/UseCases`: puntos de control de la logica de negocio, incluyendo casos de uso de `Destino`.
- `Application/Ports/External`: contratos para servicios externos.
- `Infrastructure/Repositories`: implementaciones concretas de repositorios.
- `Infrastructure/Repositories/ExternalServices`: repositorios/adaptadores que interactuan con servicios externos.
- `Infrastructure/Config`: carga y normalizacion de configuracion desde `.env`.
- `Infrastructure/Persistence/Doctrine`: fabrica de `EntityManager` para MariaDB con Doctrine ORM.
- `Interfaces/Http/Controllers`: puntos de entrada desde HTTP u otras capas de presentacion.

## Flujo esperado

1. La capa `Interfaces` recibe datos externos.
2. La capa `Application` valida el flujo del caso de uso.
3. La capa `Domain` mantiene reglas y modelos centrales.
4. La capa `Infrastructure` persiste datos o consulta servicios externos.

## Restriccion importante

No crear entidades dentro de este modulo salvo pedido explicito del usuario. Mantener la gestion de datos con contratos, entradas, casos de uso y adaptadores sin introducir clases de entidad por defecto.

## Conexion MariaDB

La conexion se configura en `.env`:

- `DB_HOST`: host de MariaDB.
- `DB_PORT`: puerto de MariaDB.
- `DB_UNIX_SOCKET`: socket Unix opcional; si esta definido, se usa en lugar de host/puerto.
- `DB_DATABASE`: base de datos.
- `DB_TABLE_DESTINOS`: tabla de destinos.
- `DB_USERNAME`: usuario.
- `DB_PASSWORD`: contrasena.
- `DB_CHARSET`: charset de conexion.
- `DB_SERVER_VERSION`: version de MariaDB usada por Doctrine para generar SQL.
- `DB_CONNECTION_TIMEOUT`: timeout de conexion en segundos.

Para obtener un `EntityManager` configurado:

```php
$entityManager = \TiendaTurismo\GestionDatos\Infrastructure\Persistence\Doctrine\EntityManagerFactory::createFromEnv();
```

Requiere tener habilitada la extension PHP `pdo_mysql` para abrir la conexion real con MariaDB.

Los repositorios que extiendan `Infrastructure\Repositories\BaseRepository` leen `.env` por defecto desde su constructor si no reciben un `EntityManager` inyectado.

## Casos De Uso Destino

- `CrearDestinoUseCase`: crea y persiste un destino desde `CrearDestinoInput`.
- `ObtenerDestinoPorIdUseCase`: obtiene un destino por `id`.
- `ListarDestinosUseCase`: lista todos los destinos.

Los destinos no se pueden eliminar bajo ningun caso. No agregar casos de uso, metodos de repositorio, scripts ni endpoints de eliminacion para `Destino`.

## Punto De Acceso Destino

`Application\Services\DestinoService` centraliza los casos de uso de `Destino` para ser consumido desde CLI, HTTP u otros adaptadores.

```php
$service = new \TiendaTurismo\GestionDatos\Application\Services\DestinoService(
    new \TiendaTurismo\GestionDatos\Infrastructure\Repositories\DestinoDoctrineRepository()
);

$destinos = $service->listar();
$destino = $service->obtenerPorId(1);
$nuevo = $service->crear([
    'ciudad' => 'Bariloche',
    'estado_provincia' => 'Rio Negro',
    'pais' => 'Argentina',
]);
```

Pruebas desde terminal:

```bash
php bin/probar_destino_service.php listar
php bin/probar_destino_service.php obtener 1
php bin/probar_destino_service.php crear "Bariloche" "Rio Negro" "Argentina"
```

## Crear Tabla Destinos

Para ver el SQL generado desde Doctrine:

```bash
php bin/crear_tabla_destinos.php --dump-sql
```

Para crear la tabla `destinos` en MariaDB:

```bash
php bin/crear_tabla_destinos.php
```

Tambien se puede ejecutar mediante Composer:

```bash
php /tmp/opencode/composer.phar db:create-destinos
```

Para probar la conexion configurada en `.env` sin mostrar la contrasena:

```bash
php bin/probar_conexion_db.php
```
