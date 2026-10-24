<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver\Naming;

use FFI\Location\Resolver\Naming\WindowsNaming;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WindowsNaming::class)]
final class WindowsNamingTest extends TestCase
{
    public function testNameWithSeparatorOrDriveIsPath(): void
    {
        $naming = new WindowsNaming();

        self::assertTrue($naming->isPath('dir/foo.dll'));
        self::assertTrue($naming->isPath('dir\\foo.dll'));
        self::assertTrue($naming->isPath('C:\\dir\\foo.dll'));
        self::assertTrue($naming->isPath('C:foo.dll'));
    }

    public function testPlainNameIsNotPath(): void
    {
        self::assertFalse((new WindowsNaming())->isPath('foo.dll'));
    }

    public function testDllExtensionIsAppendedOnlyToNameWithoutExtension(): void
    {
        $naming = new WindowsNaming();

        self::assertSame(['foo.dll'], $naming->getFiles('foo'));
        self::assertSame(['foo.dll'], $naming->getFiles('foo.dll'));
        self::assertSame(['foo.bar'], $naming->getFiles('foo.bar'));
        self::assertSame(['libfoo-1.2'], $naming->getFiles('libfoo-1.2'));
    }

    public function testExtensionOfPathIsTakenFromFilename(): void
    {
        $naming = new WindowsNaming();

        self::assertSame(['C:\\dir.v2\\foo.dll'], $naming->getFiles('C:\\dir.v2\\foo'));
        self::assertSame(['dir.v2/foo.dll'], $naming->getFiles('dir.v2/foo'));
        self::assertSame(['dir/foo.dll'], $naming->getFiles('dir/foo.dll'));
    }

    public function testTrailingDotSuppressesExtension(): void
    {
        $naming = new WindowsNaming();

        self::assertSame(['foo'], $naming->getFiles('foo.'));
        self::assertSame(['dir/foo'], $naming->getFiles('dir/foo.'));
        self::assertSame([], $naming->getFiles('.'));
    }
}
