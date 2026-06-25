<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\AI\Transport;

use TiendaTurismo\GestionDatos\Application\AI\Contracts\HttpTransportInterface;
use TiendaTurismo\GestionDatos\Application\AI\DTO\HttpRequest;
use TiendaTurismo\GestionDatos\Application\AI\DTO\HttpResponse;
use TiendaTurismo\GestionDatos\Application\AI\Exceptions\AiProviderException;

final class CurlHttpTransport implements HttpTransportInterface
{
    public function __construct(
        private readonly int $timeoutSeconds = 30,
    ) {
    }

    public function send(HttpRequest $request): HttpResponse
    {
        if (!function_exists('curl_init')) {
            throw new AiProviderException('La extension cURL no esta disponible.');
        }

        $handle = curl_init($request->url);
        if ($handle === false) {
            throw new AiProviderException('No se pudo inicializar cURL.');
        }

        $headers = array_merge([
            'Content-Type: application/json',
        ], $request->headers);

        curl_setopt_array($handle, [
            CURLOPT_CUSTOMREQUEST => $request->method,
            CURLOPT_POSTFIELDS => $request->body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
        ]);

        $raw = curl_exec($handle);
        if ($raw === false) {
            $error = curl_error($handle);
            curl_close($handle);
            throw new AiProviderException($error !== '' ? $error : 'Error desconocido al ejecutar cURL.');
        }

        $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $headerSize = (int) curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        curl_close($handle);

        return new HttpResponse(
            statusCode: $statusCode,
            body: substr($raw, $headerSize),
        );
    }
}
