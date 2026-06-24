<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class SubirImagenService
{
    private const ARCHIVOS_PERMITIDOS = ['jpg', 'jpeg', 'png', 'webp'];
    private const RUTA_BASE = '/uploads/paquetes';

    private string $directorioPublico;

    public function __construct(?string $directorioPublico = null)
    {
        $this->directorioPublico = $directorioPublico ?? (dirname(__DIR__, 3) . '/public');
    }

    public function guardar(?UploadedFile $archivo): ?string
    {
        if ($archivo === null || !$archivo->isValid()) {
            return null;
        }

        $extension = strtolower($archivo->getClientOriginalExtension());
        if (!in_array($extension, self::ARCHIVOS_PERMITIDOS, true)) {
            throw new \InvalidArgumentException(
                'Tipo de archivo no permitido. Extensiones permitidas: ' . implode(', ', self::ARCHIVOS_PERMITIDOS)
            );
        }

        $directorio = $this->obtenerDirectorioAbsoluto();
        $nombreUnico = sprintf('%s_%s.%s', uniqid('paq_', true), bin2hex(random_bytes(4)), $extension);
        $archivo->move($directorio, $nombreUnico);

        return self::RUTA_BASE . '/' . $nombreUnico;
    }

    public function eliminar(?string $rutaRelativa): void
    {
        if ($rutaRelativa === null) {
            return;
        }

        $rutaAbsoluta = $this->rutaAbsoluta($rutaRelativa);
        if (file_exists($rutaAbsoluta)) {
            unlink($rutaAbsoluta);
        }
    }

    private function obtenerDirectorioAbsoluto(): string
    {
        $dir = $this->directorioPublico . self::RUTA_BASE;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    private function rutaAbsoluta(string $rutaRelativa): string
    {
        return $this->directorioPublico . $rutaRelativa;
    }
}
