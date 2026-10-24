<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver\Directory;

use FFI\Location\Resolver\Directory\HomeDirectories;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HomeDirectories::class)]
final class HomeDirectoriesTest extends TestCase
{
    public function testSubdirectoriesOfHomeAreReturnedInOrder(): void
    {
        $this->setEnv('HOME', '/home/user');

        self::assertSame(
            ['/home/user/lib', '/home/user/.local/lib'],
            [...(new HomeDirectories('lib', '.local/lib'))->getDirectories()],
        );
    }

    public function testNoDirectoriesWithoutHome(): void
    {
        $this->setEnv('HOME', null);

        self::assertSame([], [...(new HomeDirectories('lib'))->getDirectories()]);
    }
}
