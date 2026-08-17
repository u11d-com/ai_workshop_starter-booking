<?php

declare(strict_types=1);

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'pgsql',
            'host' => getenv('DB_HOST') ?: 'postgres',
            'name' => getenv('DB_NAME') ?: 'booking',
            'user' => getenv('DB_USER') ?: 'booking',
            'pass' => getenv('DB_PASSWORD') ?: 'booking',
            'port' => (int) (getenv('DB_PORT') ?: 5432),
            'charset' => 'utf8',
        ],
    ],
    'version_order' => 'creation',
];
