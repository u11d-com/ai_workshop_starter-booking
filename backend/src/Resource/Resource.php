<?php

declare(strict_types=1);

namespace App\Resource;

/**
 * A bookable resource (e.g. a room or piece of equipment).
 */
final readonly class Resource
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}

    /**
     * @param array{id: int|string, name: string} $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            name: $row['name'],
        );
    }

    /**
     * @return array{id: int, name: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
