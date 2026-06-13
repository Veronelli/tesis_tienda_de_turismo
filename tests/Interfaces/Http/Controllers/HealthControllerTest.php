<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Interfaces\Http\Controllers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use TiendaTurismo\GestionDatos\Interfaces\Http\Controllers\HealthController;

final class HealthControllerTest extends TestCase
{
    private HealthController $controller;

    protected function setUp(): void
    {
        $_ENV['APP_ENV'] = 'test';
        $this->controller = new HealthController();
    }

    public function test_check_retorna_status_ok(): void
    {
        $response = $this->controller->check();

        $this->assertSame(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertSame('ok', $content['status']);
        $this->assertArrayHasKey('timestamp', $content);
        $this->assertArrayHasKey('php_version', $content);
        $this->assertArrayHasKey('app_env', $content);
        $this->assertSame('test', $content['app_env']);
    }

    public function test_check_retorna_timestamp_valido(): void
    {
        $response = $this->controller->check();

        $content = json_decode($response->getContent(), true);

        $this->assertNotFalse(strtotime($content['timestamp']));
    }

    public function test_check_retorna_php_version(): void
    {
        $response = $this->controller->check();

        $content = json_decode($response->getContent(), true);

        $this->assertSame(PHP_VERSION, $content['php_version']);
    }
}
