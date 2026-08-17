<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Thin factory for a shared PDO connection, configured from environment
 * variables (see docker-compose.yml).
 */
final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = getenv('DB_HOST') ?: 'postgres';
        $port = getenv('DB_PORT') ?: '5432';
        $name = getenv('DB_NAME') ?: 'booking';
        $user = getenv('DB_USER') ?: 'booking';
        $password = getenv('DB_PASSWORD') ?: 'booking';

        $dsn = \sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $name);

        self::$connection = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return self::$connection;
    }
}
