@echo off
title Configuracion XAMPP - Tienda de Turismo

echo ========================================
echo  Configuracion para XAMPP en Windows
echo  Tienda de Turismo PHP My Admin
echo ========================================
echo.

:: Obtener la ruta del directorio donde esta este .bat
set "SCRIPT_DIR=%~dp0"

:: Ejecutar el script de PowerShell con execution policy bypass
powershell -ExecutionPolicy Bypass -NoProfile -File "%SCRIPT_DIR%setup-xampp.ps1"

:: Si hubo un error, pausar para que el usuario lo vea
if %errorlevel% neq 0 (
    echo.
    echo Se produjo un error durante la configuracion.
    pause
)
