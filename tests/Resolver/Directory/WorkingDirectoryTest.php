<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver\Directory;

use FFI\Location\Resolver\Directory\WorkingDirectory;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WorkingDirectory::class)]
final class WorkingDirectoryTest extends TestCase
{
    public function testCurrentDirectoryIsReturned(): void
    {
        $directory = $this->createDirectory();

        \chdir($directory);

        self::assertSame([$directory], \iterator_to_array((new WorkingDirectory())->getDirectories(), false));
    }
}
