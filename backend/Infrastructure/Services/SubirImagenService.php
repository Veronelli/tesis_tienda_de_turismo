<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class SubirImagenService
{
    private const ARCHIVOS_PERMITIDOS = ['jpg', 'jpeg', 'png', 'webp'];
    private const TAMANO_MAXIMO_BASE64 = 16777216;

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

        $contenido = file_get_contents($archivo->getPathname());
        if ($contenido === false) {
            throw new \RuntimeException('No se pudo leer la imagen cargada.');
        }

        $base64 = base64_encode($contenido);
        $mimeType = $archivo->getMimeType() ?: $this->mimeTypeDesdeExtension($extension);
        $dataUri = sprintf('data:%s;base64,%s', $mimeType, $base64);

        if (strlen($dataUri) > self::TAMANO_MAXIMO_BASE64) {
            throw new \InvalidArgumentException('La imagen codificada no puede superar 16 MB.');
        }

        return $dataUri;
    }

    public function eliminar(?string $rutaRelativa): void
    {
        if ($rutaRelativa === null) {
            return;
        }

        if (str_starts_with($rutaRelativa, 'data:')) {
            return;
        }

        $rutaAbsoluta = $this->rutaAbsoluta($rutaRelativa);
        if (file_exists($rutaAbsoluta)) {
            unlink($rutaAbsoluta);
        }
    }

    private function rutaAbsoluta(string $rutaRelativa): string
    {
        return dirname(__DIR__, 3) . '/public' . $rutaRelativa;
    }

    private function mimeTypeDesdeExtension(string $extension): string
    {
        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }
}
