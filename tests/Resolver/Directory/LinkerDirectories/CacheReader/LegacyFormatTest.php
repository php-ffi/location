<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver\Directory\LinkerDirectories\CacheReader;

use FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader\Buffer;
use FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader\LegacyFormat;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LegacyFormat::class)]
final class LegacyFormatTest extends TestCase
{
    public function testLegacyCacheIsSupported(): void
    {
        $buffer = new Buffer(CacheBuilder::createLegacyCache(['/usr/lib/libfoo.so']));

        self::assertTrue((new LegacyFormat())->supports($buffer));
    }

    public function testCompatCacheIsSupported(): void
    {
        $buffer = new Buffer(CacheBuilder::createGlibcCache([], "ld.so-1.7.0\0\0\0\0\0"));

        self::assertTrue((new LegacyFormat())->supports($buffer));
    }

    public function testUnknownCacheIsNotSupported(): void
    {
        self::assertFalse((new LegacyFormat())->supports(new Buffer('unknown')));
    }

    public function testLibrariesAreRead(): void
    {
        $buffer = new Buffer(CacheBuilder::createLegacyCache(['libfoo.so', '/usr/lib/libfoo.so', '/opt/libbar.so']));

        self::assertSame(
            ['libfoo.so', '/usr/lib/libfoo.so', '/opt/libbar.so'],
            [...(new LegacyFormat())->getLibraries($buffer)],
        );
    }
}
