<?php

declare(strict_types=1);

namespace App;

use Closure;

/**
 * Minimal request router: matches an HTTP method + path pattern to a handler.
 * Path patterns support `{name}` placeholders, e.g. `/api/resources/{id}`.
 */
final class Router
{
    /** @var list<array{method: string, pattern: string, handler: Closure}> */
    private array $routes = [];

    public function get(string $pattern, Closure $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, Closure $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, Closure $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    /**
     * Dispatches the given method/path against registered routes.
     * Sends a JSON response and returns.
     */
    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->match($route['pattern'], $path);
            if ($params === null) {
                continue;
            }

            $result = ($route['handler'])($params);
            $this->respond($result);

            return;
        }

        $this->respond(['error' => 'Not found'], 404);
    }

    /**
     * @return array<string, string>|null
     */
    private function match(string $pattern, string $path): ?array
    {
        $patternParts = explode('/', trim($pattern, '/'));
        $pathParts = explode('/', trim($path, '/'));

        if (\count($patternParts) !== \count($pathParts)) {
            return null;
        }

        $params = [];

        foreach ($patternParts as $index => $patternPart) {
            $pathPart = $pathParts[$index];

            if (str_starts_with($patternPart, '{') && str_ends_with($patternPart, '}')) {
                $params[substr($patternPart, 1, -1)] = $pathPart;

                continue;
            }

            if ($patternPart !== $pathPart) {
                return null;
            }
        }

        return $params;
    }

    /**
     * @param mixed $result
     */
    private function respond(mixed $result, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($result, JSON_THROW_ON_ERROR);
    }
}
