<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Application\AI\Validators;

use TiendaTurismo\GestionDatos\Application\AI\Contracts\ResponseValidatorInterface;
use TiendaTurismo\GestionDatos\Application\AI\Exceptions\InvalidAiResponseException;

final class CalificarProspectoResponseValidator implements ResponseValidatorInterface
{
    private const CALIFICACIONES_VALIDAS = ['FRIO', 'TIBIO', 'CALIENTE'];

    public function validate(string $responseText): array
    {
        $data = json_decode(trim($responseText), true);

        if (!is_array($data)) {
            throw new InvalidAiResponseException('La respuesta de IA debe ser un JSON valido.');
        }

        if (array_keys($data) !== ['calificacion']) {
            throw new InvalidAiResponseException('La respuesta de IA debe contener solo la clave calificacion.');
        }

        $calificacion = $data['calificacion'] ?? null;
        if (!is_string($calificacion) || !in_array($calificacion, self::CALIFICACIONES_VALIDAS, true)) {
            throw new InvalidAiResponseException('La calificacion debe ser FRIO, TIBIO o CALIENTE.');
        }

        return [
            'calificacion' => $calificacion,
        ];
    }
}
