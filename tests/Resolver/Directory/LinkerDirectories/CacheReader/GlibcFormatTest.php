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
    public function testLibrariesAreRead(): void
    {
        $buffer = new Buffer(CacheBuilder::createGlibcCache(['libfoo.so', '/usr/lib/libfoo.so', '/opt/libbar.so']));

        $libraries = (new GlibcFormat())->findLibraries($buffer);

        self::assertNotNull($libraries);
        self::assertSame(['libfoo.so', '/usr/lib/libfoo.so', '/opt/libbar.so'], [...$libraries]);
    }

    public function testCompatCacheIsRead(): void
    {
        $buffer = new Buffer(CacheBuilder::createGlibcCache(['/usr/lib/libfoo.so'], "ld.so-1.7.0\0\0\0\0\0"));

        $libraries = (new GlibcFormat())->findLibraries($buffer);

        self::assertNotNull($libraries);
        self::assertSame(['/usr/lib/libfoo.so'], [...$libraries]);
    }

    public function testEmptyCacheHasNoLibraries(): void
    {
        $libraries = (new GlibcFormat())->findLibraries(new Buffer(CacheBuilder::createGlibcCache([])));

        self::assertNotNull($libraries);
        self::assertSame([], [...$libraries]);
    }

    public function testLegacyCacheIsNotRecognized(): void
    {
        $buffer = new Buffer(CacheBuilder::createLegacyCache(['/usr/lib/libfoo.so']));

        self::assertNull((new GlibcFormat())->findLibraries($buffer));
    }

    public function testUnknownDataIsNotRecognized(): void
    {
        self::assertNull((new GlibcFormat())->findLibraries(new Buffer('unknown')));
    }
}
