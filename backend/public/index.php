<?php

declare(strict_types=1);

use App\Database;
use App\Resource\ResourceRepository;
use App\Router;

require __DIR__ . '/../vendor/autoload.php';

$router = new Router();

$router->get('/api/health', static function (): array {
    return ['status' => 'ok'];
});

$router->get('/api/resources', static function (): array {
    $repository = new ResourceRepository(Database::connection());

    return array_map(
        static fn ($resource) => $resource->toArray(),
        $repository->all(),
    );
});

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$path = parse_url(is_string($requestUri) ? $requestUri : '/', PHP_URL_PATH);

$router->dispatch(
    is_string($requestMethod) ? $requestMethod : 'GET',
    is_string($path) ? $path : '/',
);
