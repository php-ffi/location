<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver\Directory\LinkerDirectories\CacheReader;

use FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader\Buffer;
use FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader\GlibcFormat;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GlibcFormat::class)]
final class GlibcFormatTest extends TestCase
{
    public function testGlibcCacheIsSupported(): void
    {
        $buffer = new Buffer(CacheBuilder::createGlibcCache(['/usr/lib/libfoo.so']));

        self::assertTrue((new GlibcFormat())->supports($buffer));
    }

    public function testCompatCacheIsSupported(): void
    {
        $buffer = new Buffer(CacheBuilder::createGlibcCache([], "ld.so-1.7.0\0\0\0\0\0"));

        self::assertTrue((new GlibcFormat())->supports($buffer));
    }

    public function testLegacyCacheIsNotSupported(): void
    {
        $buffer = new Buffer(CacheBuilder::createLegacyCache(['/usr/lib/libfoo.so']));

        self::assertFalse((new GlibcFormat())->supports($buffer));
    }

    public function testLibrariesAreRead(): void
    {
        $buffer = new Buffer(CacheBuilder::createGlibcCache(['libfoo.so', '/usr/lib/libfoo.so', '/opt/libbar.so']));

        self::assertSame(
            ['libfoo.so', '/usr/lib/libfoo.so', '/opt/libbar.so'],
            [...(new GlibcFormat())->getLibraries($buffer)],
        );
    }

    public function testUnsupportedCacheHasNoLibraries(): void
    {
        self::assertSame([], [...(new GlibcFormat())->getLibraries(new Buffer('unknown'))]);
    }
}
