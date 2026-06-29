# Tienda de Turismo PHP My Admin

Proyecto PHP 8.2 con Apache, MariaDB y Doctrine ORM.

## Requisitos

- XAMPP en Windows o LAMPP en Linux
- PHP 8.2+
- MariaDB/MySQL
- Composer

## Estructura

- `public/`: punto de entrada web
- `backend/`: lógica de aplicación, dominio e infraestructura
- `config/`: rutas y configuración
- `bin/`: scripts CLI
- `tienda_de_turismo_admin_export.sql`: estructura de la base
- `tienda_de_turismo_admin_data.sql`: solo datos (`INSERT`)

## Instalación en XAMPP o LAMPP

1. Copiá el proyecto dentro del directorio web de Apache.
   - Windows: `C:\xampp\htdocs\tienda_de_turismo_php_my_admin`
   - Linux: `/opt/lampp/htdocs/tienda_de_turismo_php_my_admin`
2. Verificá que Apache tenga `mod_rewrite` habilitado.
3. Asegurate de tener habilitada la extensión `pdo_mysql` en PHP.
4. Instalá dependencias:

```bash
composer install
```

5. Configurá el archivo `.env` con tu entorno local.

## Configuración `.env`

El proyecto carga `.env` desde `public/index.php` y lee estas variables:

### Base de datos

Obligatorias:

```env
DB_DRIVER=pdo_mysql
DB_DATABASE=tienda_de_turismo
DB_USERNAME=root
DB_PASSWORD=gawzux-mapbe5-Sihwic
```

Opcionales con valor por defecto:

```env
APP_ENV=develop
DB_HOST=mariadb
DB_PORT=3306
DB_CHARSET=utf8mb4
DB_SERVER_VERSION=mariadb-10.11.0
DB_CONNECTION_TIMEOUT=5
DB_UNIX_SOCKET=
DB_TABLE_DESTINOS=destinos
```

### JWT

```env
JWT_SECRET=clave_super_secreta_cambiar_en_produccion
JWT_TTL=3600
```

### IA

```env
AI_PROVIDER=openai
GEMINI_API_KEY=
GEMINI_MODEL=gemini-3.1-flash-lite
OPENAI_API_KEY=
OPENAI_MODEL=gpt-5.5
AI_TIMEOUT_SECONDS=30
```

Si vas a usar los SQL exportados en este proyecto, revisá que `DB_DATABASE` coincida con el dump que importes. La plantilla `.env.develop` usa `tienda_de_turismo`.

## Base de datos

Tenés exportaciones listas:

- `tienda_de_turismo_admin_export.sql`: solo estructura
- `tienda_de_turismo_admin_data.sql`: solo datos

Orden recomendado para restaurar todo:

1. Crear la base `tienda_de_turismo`
2. Importar `tienda_de_turismo_admin_export.sql`
3. Importar `tienda_de_turismo_admin_data.sql`

## Ejecución

### Opción recomendada

Configurá Apache para que el DocumentRoot apunte a `public/`.

### Opción rápida

Abrí el proyecto desde el navegador y usá la ruta pública:

```text
http://localhost/tienda_de_turismo_php_my_admin/
```

La raíz redirige a la página pública de búsqueda de paquetes.

## Rutas principales

- `/` -> búsqueda de paquetes
- `/public/buscar-paquetes.html`
- `/public/consultar-paquete.html`
- `/public/consulta-enviada.html`
- `/public/dashboard/consultas.php`
- `/public/dashboard/clientes.php`
- `/public/dashboard/paquetes.php`
- `/public/dashboard/hoteles.php`
- `/public/dashboard/destinos.php`

## Endpoints API

El front controller está en `public/index.php` y expone las rutas definidas en `config/routes.php`.

## Scripts útiles

```bash
composer db:create-all
composer db:create-destinos
composer db:test-connection
composer test
```

## Nota

En Windows, si el proyecto no abre bien desde la URL del navegador, revisá:

- que Apache esté iniciado
- que `mod_rewrite` esté activo
- que `AllowOverride All` esté habilitado para el directorio del proyecto
- que `pdo_mysql` esté cargado en PHP
