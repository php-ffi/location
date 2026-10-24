<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver\Naming;

use FFI\Location\Resolver\Naming\UnixNaming;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UnixNaming::class)]
final class UnixNamingTest extends TestCase
{
    public function testNameWithSlashIsPath(): void
    {
        $naming = new UnixNaming();

        self::assertTrue($naming->isPath('./libfoo.so'));
        self::assertTrue($naming->isPath('/usr/lib/libfoo.so'));
        self::assertTrue($naming->isPath('dir/libfoo.so'));
    }

    public function testNameWithoutSlashIsNotPath(): void
    {
        $naming = new UnixNaming();

        self::assertFalse($naming->isPath('libfoo.so'));
        self::assertFalse($naming->isPath('dir\\libfoo.so'));
    }

    public function testFileNameIsNotChanged(): void
    {
        $naming = new UnixNaming();

        self::assertSame(['libfoo'], $naming->getFiles('libfoo'));
        self::assertSame(['libfoo.so.1'], $naming->getFiles('libfoo.so.1'));
    }
}
