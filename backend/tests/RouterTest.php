<?php

declare(strict_types=1);

namespace App\Tests;

use App\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testDispatchesMatchingRouteWithParams(): void
    {
        $router = new Router();
        $captured = null;

        $router->get('/api/resources/{id}', function (array $params) use (&$captured): array {
            $captured = $params;

            return ['ok' => true];
        });

        ob_start();
        $router->dispatch('GET', '/api/resources/42');
        $body = ob_get_clean();

        self::assertSame(['id' => '42'], $captured);
        self::assertJsonStringEqualsJsonString('{"ok":true}', (string) $body);
    }

    public function testUnmatchedRouteReturnsNotFound(): void
    {
        $router = new Router();

        ob_start();
        $router->dispatch('GET', '/nope');
        $body = ob_get_clean();

        self::assertJsonStringEqualsJsonString('{"error":"Not found"}', (string) $body);
    }
}
