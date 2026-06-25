<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Application\AI\Validators;

use PHPUnit\Framework\TestCase;
use TiendaTurismo\GestionDatos\Application\AI\Exceptions\InvalidAiResponseException;
use TiendaTurismo\GestionDatos\Application\AI\Validators\CalificarProspectoResponseValidator;

final class CalificarProspectoResponseValidatorTest extends TestCase
{
    public function test_validate_acepta_json_valido(): void
    {
        $validator = new CalificarProspectoResponseValidator();

        $data = $validator->validate('{"calificacion":"CALIENTE"}');

        $this->assertSame(['calificacion' => 'CALIENTE'], $data);
    }

    public function test_validate_rechaza_formato_incorrecto(): void
    {
        $validator = new CalificarProspectoResponseValidator();

        $this->expectException(InvalidAiResponseException::class);

        $validator->validate('respuesta libre');
    }

    public function test_validate_rechaza_valor_invalido(): void
    {
        $validator = new CalificarProspectoResponseValidator();

        $this->expectException(InvalidAiResponseException::class);

        $validator->validate('{"calificacion":"MEDIO"}');
    }
}
