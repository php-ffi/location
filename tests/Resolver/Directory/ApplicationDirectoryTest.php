<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver\Directory;

use FFI\Location\Resolver\Directory\ApplicationDirectory;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApplicationDirectory::class)]
final class ApplicationDirectoryTest extends TestCase
{
    public function testDirectoryOfPhpBinaryIsReturned(): void
    {
        $directories = \iterator_to_array((new ApplicationDirectory())->getDirectories(), false);

        self::assertSame([\dirname(\PHP_BINARY)], $directories);
    }
}
