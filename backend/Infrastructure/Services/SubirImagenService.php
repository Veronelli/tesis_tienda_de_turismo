<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class SubirImagenService
{
    private const ARCHIVOS_PERMITIDOS = ['jpg', 'jpeg', 'png', 'webp'];
    private const MIMES_PERMITIDOS = ['image/jpeg', 'image/png', 'image/webp'];
    private const TAMANIO_MAXIMO = 5 * 1024 * 1024;

    public function guardar(?UploadedFile $archivo): ?string
    {
        if ($archivo === null || !$archivo->isValid()) {
            return null;
        }

        if ($archivo->getSize() > self::TAMANIO_MAXIMO) {
            throw new \InvalidArgumentException('La imagen no puede superar los 5MB.');
        }

        $extension = strtolower($archivo->getClientOriginalExtension());
        if (!in_array($extension, self::ARCHIVOS_PERMITIDOS, true)) {
            throw new \InvalidArgumentException(
                'Tipo de archivo no permitido. Extensiones permitidas: ' . implode(', ', self::ARCHIVOS_PERMITIDOS)
            );
        }

        $mime = $archivo->getMimeType();
        if (!in_array($mime, self::MIMES_PERMITIDOS, true)) {
            throw new \InvalidArgumentException('Tipo MIME no permitido: ' . $mime);
        }

        $contenido = file_get_contents($archivo->getPathname());
        if ($contenido === false) {
            throw new \RuntimeException('No se pudo leer el archivo subido.');
        }

        $base64 = base64_encode($contenido);

        return 'data:' . $mime . ';base64,' . $base64;
    }
}
