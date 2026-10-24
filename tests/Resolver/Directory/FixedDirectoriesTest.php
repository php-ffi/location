<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver\Directory;

use FFI\Location\Resolver\Directory\FixedDirectories;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FixedDirectories::class)]
final class FixedDirectoriesTest extends TestCase
{
    public function testGivenDirectoriesAreReturnedInOrder(): void
    {
        $provider = new FixedDirectories('/a', '/b');

        self::assertSame(['/a', '/b'], [...$provider->getDirectories()]);
    }

    public function testNoDirectoriesAreReturnedWithoutArguments(): void
    {
        self::assertSame([], [...(new FixedDirectories())->getDirectories()]);
    }
}
