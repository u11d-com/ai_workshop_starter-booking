<?php

declare(strict_types=1);

namespace App\Resource;

use PDO;

/**
 * PDO-backed persistence for {@see Resource} entities.
 */
final class ResourceRepository
{
    public function __construct(
        private readonly PDO $connection,
    ) {}

    /**
     * @return list<Resource>
     */
    public function all(): array
    {
        $statement = $this->connection->query('SELECT id, name FROM resources ORDER BY id');

        /** @var list<array{id: int|string, name: string}> $rows */
        $rows = $statement === false ? [] : $statement->fetchAll();

        return array_map(
            static fn (array $row): Resource => Resource::fromRow($row),
            $rows,
        );
    }
}
