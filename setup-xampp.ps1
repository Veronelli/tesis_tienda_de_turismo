<#
.SYNOPSIS
    Auto-configuración para XAMPP en Windows - Tienda de Turismo PHP My Admin
.DESCRIPTION
    Configura automáticamente el proyecto para funcionar en un servidor XAMPP:
    - Detecta la instalación de XAMPP
    - Configura el archivo .env con los valores correctos para XAMPP
    - Instala dependencias de Composer
    - Habilita mod_rewrite en Apache
    - Crea la base de datos y las tablas
    - Verifica que el endpoint de health responda
.EXAMPLE
    .\setup-xampp.ps1
    Ejecuta la configuración interactiva.
.EXAMPLE
    .\setup-xampp.ps1 -XamppPath "D:\xampp" -DbPassword "mi_pass"
    Especifica rutas y contraseña sin interactividad.
#>

param(
    [string]$XamppPath = "",
    [string]$DbPassword = "",
    [switch]$SkipComposer,
    [switch]$SkipDb,
    [switch]$Help
)

if ($Help) {
    Get-Help $MyInvocation.MyCommand.Path
    exit 0
}

# ============================================================
# CONFIGURACIÓN
# ============================================================
$ProjectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$ProjectName = Split-Path -Leaf $ProjectRoot
$HtdocsTarget = ""

# Colores para la salida
$Host.UI.RawUI.ForegroundColor = "Gray"
function Write-Step {
    param([string]$Message)
    Write-Host "`n>>> $Message" -ForegroundColor Cyan
}
function Write-OK {
    param([string]$Message)
    Write-Host "  [OK] $Message" -ForegroundColor Green
}
function Write-Warn {
    param([string]$Message)
    Write-Host "  [WARN] $Message" -ForegroundColor Yellow
}
function Write-Error {
    param([string]$Message)
    Write-Host "  [ERROR] $Message" -ForegroundColor Red
}
function Write-Info {
    param([string]$Message)
    Write-Host "  $Message" -ForegroundColor DarkGray
}

# ============================================================
# 1. DETECTAR XAMPP
# ============================================================
Write-Step "Detectando instalacion de XAMPP..."

$possiblePaths = @(
    "C:\xampp",
    "C:\Program Files\xampp",
    "C:\Program Files (x86)\xampp",
    "$env:ProgramFiles\xampp",
    "${env:ProgramFiles(x86)}\xampp",
    "D:\xampp",
    "E:\xampp"
)

if ($XamppPath -ne "" -and (Test-Path $XamppPath)) {
    $foundXampp = $XamppPath
    Write-OK "Usando ruta especificada: $foundXampp"
} else {
    $foundXampp = $null
    foreach ($p in $possiblePaths) {
        if (Test-Path "$p\apache\bin\httpd.exe") {
            $foundXampp = $p
            Write-OK "XAMPP encontrado en: $foundXampp"
            break
        }
    }
}

if (-not $foundXampp) {
    Write-Error "No se encontro XAMPP en las rutas habituales."
    $customPath = Read-Host "Ingrese la ruta de instalacion de XAMPP (o presione Enter para cancelar)"
    if ($customPath -and (Test-Path "$customPath\apache\bin\httpd.exe")) {
        $foundXampp = $customPath
        Write-OK "XAMPP encontrado en: $foundXampp"
    } elseif ($customPath) {
        Write-Error "No se encontro Apache en: $customPath"
        exit 1
    } else {
        Write-Error "Configuracion cancelada."
        exit 1
    }
}

$phpPath = "$foundXampp\php\php.exe"
$httpdPath = "$foundXampp\apache\bin\httpd.exe"
$mysqlPath = "$foundXampp\mysql\bin\mysql.exe"
$httpdConf = "$foundXampp\apache\conf\httpd.conf"

if (-not (Test-Path $phpPath)) {
    Write-Error "No se encontro PHP en: $phpPath"
    exit 1
}
Write-OK "PHP: $phpPath"

if (-not (Test-Path $httpdPath)) {
    Write-Error "No se encontro Apache en: $httpdPath"
    exit 1
}
Write-OK "Apache: $httpdPath"

if (-not (Test-Path $mysqlPath)) {
    Write-Warn "No se encontro MySQL CLI en: $mysqlPath"
    $mysqlPath = $null
} else {
    Write-OK "MySQL: $mysqlPath"
}

# ============================================================
# 2. VERIFICAR VERSIÓN DE PHP
# ============================================================
Write-Step "Verificando version de PHP..."

$phpVersion = & $phpPath -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;"
$phpMajorMinor = [version]$phpVersion

Write-Info "PHP version detectada: $phpVersion"

if ($phpMajorMinor -lt [version]"8.2") {
    Write-Error "Se requiere PHP 8.2 o superior. Versión actual: $phpVersion"
    Write-Warn "XAMPP con PHP 8.1 o inferior no es compatible. Actualice XAMPP."
    exit 1
}
Write-OK "PHP $phpVersion es compatible"

# ============================================================
# 3. CONFIGURAR .env
# ============================================================
Write-Step "Configurando archivo .env..."

$envFile = "$ProjectRoot\.env"
$envDevelopFile = "$ProjectRoot\.env.develop"

if (-not (Test-Path $envFile)) {
    if (Test-Path $envDevelopFile) {
        Copy-Item $envDevelopFile $envFile
        Write-OK "Creado .env desde .env.develop"
    } else {
        Write-Error "No existe .env ni .env.develop. Cree un archivo .env manualmente."
        exit 1
    }
}

$envContent = Get-Content $envFile -Raw

# Reemplazar DB_HOST (mariadb -> 127.0.0.1)
if ($envContent -match 'DB_HOST=.+') {
    $envContent = $envContent -replace 'DB_HOST=.+', 'DB_HOST=127.0.0.1'
} else {
    $envContent += "`nDB_HOST=127.0.0.1"
}
Write-OK "DB_HOST configurado a 127.0.0.1"

# Reemplazar DB_PASSWORD
if ($DbPassword -eq "") {
    $DbPassword = ""
}
if ($envContent -match 'DB_PASSWORD=.+') {
    $envContent = $envContent -replace 'DB_PASSWORD=.+', "DB_PASSWORD=$DbPassword"
} else {
    $envContent += "`nDB_PASSWORD=$DbPassword"
}
Write-OK "DB_PASSWORD configurado"

# Asegurar que APP_ENV sea develop
if ($envContent -match 'APP_ENV=.+') {
    $envContent = $envContent -replace 'APP_ENV=.+', 'APP_ENV=develop'
} else {
    $envContent += "`nAPP_ENV=develop"
}

Set-Content $envFile $envContent -NoNewline
Write-OK "Archivo .env actualizado correctamente"

# ============================================================
# 4. INSTALAR COMPOSER
# ============================================================
if (-not $SkipComposer) {
    Write-Step "Verificando dependencias de Composer..."

    $composerCmd = $null
    # Buscar composer global
    $globalComposer = Get-Command "composer" -ErrorAction SilentlyContinue
    if ($globalComposer) {
        $composerCmd = "composer"
        Write-OK "Composer encontrado globalmente"
    } elseif (Test-Path "$ProjectRoot\composer.phar") {
        $composerCmd = "& `"$phpPath`" `"$ProjectRoot\composer.phar`""
        Write-OK "composer.phar encontrado localmente"
    } else {
        Write-Warn "Composer no esta instalado."
        $downloadComposer = Read-Host "¿Descargar composer.phar? (S/N)"
        if ($downloadComposer -eq "S" -or $downloadComposer -eq "s") {
            Write-Info "Descargando composer.phar..."
            try {
                Invoke-WebRequest -Uri "https://getcomposer.org/composer-stable.phar" -OutFile "$ProjectRoot\composer.phar" -UseBasicParsing
                Write-OK "composer.phar descargado"
                $composerCmd = "& `"$phpPath`" `"$ProjectRoot\composer.phar`""
            } catch {
                Write-Error "No se pudo descargar Composer: $_"
                Write-Warn "Continúe manualmente con: php composer.phar install (o composer install)"
            }
        } else {
            Write-Warn "Omitiendo instalación de dependencias."
            Write-Warn "Ejecute manualmente: composer install"
        }
    }

    if ($composerCmd) {
        Write-Info "Instalando dependencias (esto puede tomar unos minutos)..."
        $installCommand = "$composerCmd install --no-interaction --working-dir=`"$ProjectRoot`""
        Invoke-Expression $installCommand
        if ($LASTEXITCODE -eq 0) {
            Write-OK "Dependencias instaladas correctamente"
        } else {
            Write-Error "Error al instalar dependencias (código: $LASTEXITCODE)"
        }
    }
} else {
    Write-Warn "Omitiendo instalación de Composer (flag --SkipComposer)"
}

# ============================================================
# 5. VERIFICAR AUTOLOAD
# ============================================================
if (-not (Test-Path "$ProjectRoot\vendor\autoload.php")) {
    Write-Error "No se encuentra vendor/autoload.php. Ejecute 'composer install' manualmente."
    exit 1
}
Write-OK "vendor/autoload.php encontrado"

# ============================================================
# 6. CREAR DIRECTORIOS NECESARIOS
# ============================================================
Write-Step "Creando directorios necesarios..."

$proxyDir = "$ProjectRoot\var\doctrine\proxies"
if (-not (Test-Path $proxyDir)) {
    New-Item -ItemType Directory -Path $proxyDir -Force | Out-Null
    Write-OK "Directorio creado: $proxyDir"
} else {
    Write-OK "Directorio existe: $proxyDir"
}

# ============================================================
# 7. HABILITAR MOD_REWRITE EN APACHE
# ============================================================
Write-Step "Configurando Apache (mod_rewrite)..."

if (-not (Test-Path $httpdConf)) {
    Write-Error "No se encuentra httpd.conf en: $httpdConf"
    exit 1
}

$httpdContent = Get-Content $httpdConf -Raw
$changesMade = $false

# Habilitar mod_rewrite
if ($httpdContent -match '#LoadModule rewrite_module') {
    $httpdContent = $httpdContent -replace '#LoadModule rewrite_module', 'LoadModule rewrite_module'
    Write-OK "mod_rewrite habilitado en httpd.conf"
    $changesMade = $true
} elseif ($httpdContent -match 'LoadModule rewrite_module') {
    Write-OK "mod_rewrite ya esta habilitado"
} else {
    Write-Warn "No se encontró la linea LoadModule rewrite_module en httpd.conf"
    Write-Warn "Verifique manualmente que mod_rewrite esté habilitado"
}

# Asegurar AllowOverride en el directorio de htdocs
if ($httpdContent -match '<Directory "C:/xampp/htdocs">') {
    if ($httpdContent -notmatch 'AllowOverride All') {
        $httpdContent = $httpdContent -replace '(<Directory "C:/xampp/htdocs">[\s\S]*?)(AllowOverride\s+\w+)', '${1}AllowOverride All'
        Write-OK "AllowOverride configurado para htdocs"
        $changesMade = $true
    }
}

if ($changesMade) {
    Set-Content $httpdConf $httpdContent -NoNewline
    Write-OK "httpd.conf actualizado"
}

# ============================================================
# 8. INSTALAR EN HTDOCS (OPCIONAL)
# ============================================================
Write-Step "Configurando proyecto en XAMPP..."

$htdocsDir = "$foundXampp\htdocs"
$targetDir = "$htdocsDir\$ProjectName"

if (Test-Path $targetDir) {
    Write-OK "El proyecto ya existe en htdocs: $targetDir"
} else {
    Write-Warn "El proyecto no esta en htdocs."
    $option = Read-Host "¿Desea crear un acceso directo (symlink) desde htdocs? (S/N. N = accedera manualmente)"

    if ($option -eq "S" -or $option -eq "s") {
        try {
            # Intentar symlink (requiere admin o developer mode)
            New-Item -ItemType SymbolicLink -Path $targetDir -Target $ProjectRoot -ErrorAction Stop | Out-Null
            Write-OK "Symlink creado: $targetDir -> $ProjectRoot"
        } catch {
            Write-Warn "No se pudo crear el symlink (se requieren permisos de administrador)"
            $copyOption = Read-Host "¿Desea copiar la carpeta a htdocs en su lugar? (S/N)"
            if ($copyOption -eq "S" -or $copyOption -eq "s") {
                Write-Info "Copiando archivos (esto puede tomar unos momentos)..."
                Copy-Item -Path $ProjectRoot -Destination $targetDir -Recurse -Exclude "vendor", ".git", ".phpunit.cache", "coverage", "node_modules"
                Write-OK "Proyecto copiado a: $targetDir"
            } else {
                Write-Warn "Debera acceder manualmente. Copie la carpeta a: $targetDir"
            }
        }
    } else {
        Write-Warn "Omitiendo instalacion en htdocs."
        Write-Info "Copie la carpeta '$ProjectName' a: $targetDir"
    }
}

# ============================================================
# 9. CREAR BASE DE DATOS
# ============================================================
if (-not $SkipDb -and $mysqlPath) {
    Write-Step "Configurando base de datos..."

    $dbPwdArg = if ($DbPassword -ne "") { "-p$DbPassword" } else { "" }
    $dbName = "tienda_de_turismo"

    # Intentar crear la base de datos
    $createDbCmd = "& `"$mysqlPath`" -u root $dbPwdArg -e `"CREATE DATABASE IF NOT EXISTS $dbName CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`" 2>&1"
    $dbResult = Invoke-Expression $createDbCmd

    if ($LASTEXITCODE -eq 0) {
        Write-OK "Base de datos '$dbName' lista"
    } else {
        Write-Warn "No se pudo crear la base de datos automaticamente."
        Write-Info "Esto puede deberse a que la contraseña de MySQL no es la correcta."
        Write-Info "Intente crear la base de datos manualmente:"
        Write-Info "   mysql -u root -p -e ""CREATE DATABASE $dbName CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"""
    }

    # Crear tablas via Doctrine SchemaTool
    if (Test-Path "$ProjectRoot\bin\crear_tablas.php") {
        Write-Info "Creando tablas con Doctrine SchemaTool..."
        $schemaCmd = "& `"$phpPath`" `"$ProjectRoot\bin\crear_tablas.php`" `"$ProjectRoot\.env`" 2>&1"
        $schemaResult = Invoke-Expression $schemaCmd

        if ($LASTEXITCODE -eq 0) {
            Write-OK "Tablas creadas correctamente"
            $schemaResult | ForEach-Object { Write-Info $_ }
        } else {
            Write-Error "Error al crear tablas"
            $schemaResult | ForEach-Object { Write-Info $_ }
        }
    } else {
        Write-Warn "No se encuentra bin/crear_tablas.php. Las tablas no se crearon."
    }
} elseif ($SkipDb) {
    Write-Warn "Omitiendo configuración de base de datos (flag --SkipDb)"
} else {
    Write-Warn "MySQL CLI no encontrado. Cree la base de datos manualmente."
}

# ============================================================
# 10. REINICIAR APACHE
# ============================================================
Write-Step "Reiniciando Apache..."

$apacheRestarted = $false
$serviceNames = @("Apache2.4", "apache2.4", "Apache2", "apache2")

foreach ($svc in $serviceNames) {
    $service = Get-Service -Name $svc -ErrorAction SilentlyContinue
    if ($service) {
        Write-Info "Reiniciando servicio: $svc"
        Restart-Service -Name $svc -Force -ErrorAction SilentlyContinue
        if ($?) {
            Write-OK "Apache reiniciado (servicio: $svc)"
            $apacheRestarted = $true
            break
        } else {
            Write-Warn "No se pudo reiniciar el servicio $svc"
        }
    }
}

if (-not $apacheRestarted) {
    Write-Warn "Apache no se pudo reiniciar automaticamente."
    Write-Info "Reinicielo manualmente desde el panel de control de XAMPP"
}

# ============================================================
# 11. TESTEAR ENDPOINT
# ============================================================
Write-Step "Verificando endpoint..."

Start-Sleep -Seconds 2

$testUrl = "http://localhost/$ProjectName/api/health"
try {
    $response = Invoke-WebRequest -Uri $testUrl -UseBasicParsing -TimeoutSec 10
    if ($response.StatusCode -eq 200) {
        Write-OK "Endpoint respondio correctamente!"
        Write-Info "URL: $testUrl"
        Write-Info "Respuesta: $($response.Content)"
    } else {
        Write-Warn "Endpoint respondio con codigo: $($response.StatusCode)"
    }
} catch {
    Write-Warn "No se pudo verificar el endpoint automaticamente."
    Write-Info "URL: $testUrl"
    Write-Info "Si Apache esta corriendo, pruebe la URL en el navegador."
}

# ============================================================
# 12. RESUMEN FINAL
# ============================================================
Write-Host "`n========================================" -ForegroundColor Green
Write-Host "  CONFIGURACION COMPLETADA" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "`nResumen:" -ForegroundColor Cyan
Write-Host "  Proyecto:     $ProjectName" -ForegroundColor White
Write-Host "  Ruta local:   $ProjectRoot" -ForegroundColor White
if (Test-Path $targetDir) {
    Write-Host "  Ruta htdocs:  $targetDir" -ForegroundColor White
}
Write-Host "  XAMPP:        $foundXampp" -ForegroundColor White
Write-Host "  .env:         $envFile" -ForegroundColor White
Write-Host "`nAcceda en: http://localhost/$ProjectName/api/health" -ForegroundColor Yellow
Write-Host "`nSi algo no funciona, revise:" -ForegroundColor DarkGray
Write-Host "  1. Que Apache este corriendo (panel XAMPP)" -ForegroundColor DarkGray
Write-Host "  2. Que la base de datos exista y las tablas esten creadas" -ForegroundColor DarkGray
Write-Host "  3. Que el archivo .env tenga los valores correctos" -ForegroundColor DarkGray
Write-Host "  4. Que mod_rewrite este habilitado (httpd.conf)" -ForegroundColor DarkGray
Write-Host "`nPresione Enter para salir..." -ForegroundColor DarkGray
Read-Host | Out-Null
