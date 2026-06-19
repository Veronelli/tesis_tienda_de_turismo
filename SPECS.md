# SPECS — Tienda de Turismo PHP My Admin

## Stack Tecnológico

### Lenguaje
- **PHP ^8.2** con tipado estricto (`declare(strict_types=1)`)
- Características: propiedades `readonly`, promotion de constructor, union types, atributos nativos (PHP 8.0+)

### Base de Datos
- **MariaDB 10.11** (vía driver `pdo_mysql`)
- Puerto: `3306` | Charset: `utf8mb4`

### ORM
- **Doctrine ORM 3.x** (`doctrine/orm: ^3.3`)
- Mapeo mediante **atributos PHP 8** (`#[ORM\Entity]`, `#[ORM\Column]`, etc.)
- DBAL 4.x subyacente

### Cache
- **Symfony Cache 7.x** para metadatos y proxies de Doctrine

### Dependencias Directas (`composer.json`)

| Paquete | Versión | Propósito |
|---|---|---|
| `php` | `^8.2` | Runtime |
| `doctrine/orm` | `^3.3` | ORM |
| `symfony/cache` | `^7.0` | Cache para Doctrine |

---

## Arquitectura

**Hexagonal** (Ports & Adapters / Clean Architecture):

| Capa | Responsabilidad |
|---|---|
| `Domain/` | Entidades, interfaces de repositorio, reglas de negocio |
| `Application/` | Casos de uso, DTOs de entrada, servicios fachada |
| `Infrastructure/` | Implementaciones concretas (Doctrine, config, repos) |
| `Interfaces/` | Puntos de entrada (HTTP, CLI) |

> La capa `Domain` **nunca** depende de `Infrastructure`.

---

## Árbol de Carpetas

```
.
├── backend/                              → Módulo PHP principal (PSR-4)
│   ├── Application/                      → Capa de aplicación
│   │   ├── Input/                        →   DTOs de entrada (CrearDestinoInput)
│   │   ├── Ports/                        →   Contratos con servicios externos
│   │   ├── Services/                     →   Servicios fachada (DestinoService)
│   │   └── UseCases/Destino/             →   Casos de uso (CRUD)
│   ├── Domain/                           → Capa de dominio
│   │   ├── Models/                       →   Entidades (Destino)
│   │   │   └── Traits/                   →     Traits reutilizables (AtributosBase)
│   │   ├── Repositories/                 →   Interfaces de repositorio
│   │   └── Services/                     →   Servicios de dominio
│   ├── Infrastructure/                   → Capa de infraestructura
│   │   ├── Config/                       →   Cargadores de configuración (EnvLoader, MariaDbConfig)
│   │   ├── Persistence/Doctrine/         →   Fábrica de EntityManager
│   │   └── Repositories/                 →   Implementaciones (BaseRepository, DestinoDoctrineRepository)
│   └── Interfaces/Http/Controllers/      →   Controladores HTTP (vacío)
├── composer.json                         → Definición del proyecto Composer
├── composer.lock                         → Versiones bloqueadas de dependencias
├── .env                                  → Variables de entorno (gitignored)
└── .gitignore                            → Reglas de ignorado
```

---

## Variables de Entorno (`.env`)

| Variable | Default | Descripción |
|---|---|---|
| `APP_ENV` | `develop` | Entorno de la aplicación |
| `DB_DRIVER` | `pdo_mysql` | Driver de Doctrine DBAL |
| `DB_HOST` | `127.0.0.1` | Host de la base de datos |
| `DB_PORT` | `3306` | Puerto |
| `DB_DATABASE` | — | Nombre de la base de datos |
| `DB_USERNAME` | — | Usuario |
| `DB_PASSWORD` | `''` | Contraseña |
| `DB_CHARSET` | `utf8mb4` | Juego de caracteres |
| `DB_SERVER_VERSION` | `mariadb-10.11.0` | Versión del servidor |
| `DB_CONNECTION_TIMEOUT` | `5` | Timeout de conexión (seg) |
| `DB_UNIX_SOCKET` | `''` | Socket Unix |

---

## Scripts Disponibles (`composer.json`)

| Comando | Propósito |
|---|---|
| `composer db:create-destinos` | Crear tabla `destinos` |
| `composer db:dump-sql-destinos` | Mostrar SQL sin ejecutar |
| `composer db:test-connection` | Probar conexión a la base de datos |
| `composer destinos:test-service` | Probar servicio DestinoService vía CLI |
