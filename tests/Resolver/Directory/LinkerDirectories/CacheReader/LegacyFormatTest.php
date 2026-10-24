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
    public function testLibrariesAreRead(): void
    {
        $buffer = new Buffer(CacheBuilder::createLegacyCache(['libfoo.so', '/usr/lib/libfoo.so', '/opt/libbar.so']));

        $libraries = (new LegacyFormat())->findLibraries($buffer);

        self::assertNotNull($libraries);
        self::assertSame(['libfoo.so', '/usr/lib/libfoo.so', '/opt/libbar.so'], [...$libraries]);
    }

    public function testCompatCacheIsRecognizedByItsLegacyPart(): void
    {
        $buffer = new Buffer(CacheBuilder::createGlibcCache([], "ld.so-1.7.0\0\0\0\0\0"));

        self::assertNotNull((new LegacyFormat())->findLibraries($buffer));
    }

    public function testUnknownDataIsNotRecognized(): void
    {
        self::assertNull((new LegacyFormat())->findLibraries(new Buffer('unknown')));
    }
}
