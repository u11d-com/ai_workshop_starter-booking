<?php

declare(strict_types=1);

namespace App\Tests\Resource;

use App\Resource\Resource;
use PHPUnit\Framework\TestCase;

final class ResourceTest extends TestCase
{
    public function testFromRowAndToArrayRoundTrip(): void
    {
        $resource = Resource::fromRow(['id' => '3', 'name' => 'Projector']);

        self::assertSame(3, $resource->id);
        self::assertSame('Projector', $resource->name);
        self::assertSame(['id' => 3, 'name' => 'Projector'], $resource->toArray());
    }
}
