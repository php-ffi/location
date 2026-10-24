<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver\Directory\LinkerDirectories;

use FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader;
use FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader\Buffer;
use FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader\FormatInterface;
use FFI\Location\Tests\Resolver\Directory\LinkerDirectories\CacheReader\CacheBuilder;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CacheReader::class)]
final class CacheReaderTest extends TestCase
{
    /**
     * @param list<FormatInterface>|null $formats
     * @return list<string>
     */
    private function readDirectories(string $contents, ?array $formats = null): array
    {
        $cache = $this->createFile($this->createDirectory() . '/ld.so.cache', $contents);

        return \iterator_to_array(new CacheReader($cache, $formats), false);
    }

    public function testMissingCacheHasNoDirectories(): void
    {
        $reader = new CacheReader($this->createDirectory() . '/missing.cache');

        self::assertSame([], \iterator_to_array($reader, false));
    }

    public function testUnknownFormatHasNoDirectories(): void
    {
        self::assertSame([], $this->readDirectories('unknown /usr/lib/libfoo.so'));
    }

    public function testOnlyUniqueAbsoluteDirectoriesAreReturned(): void
    {
        $contents = CacheBuilder::createGlibcCache([
            'libfoo.so.1',
            '/usr/lib/libfoo.so.1',
            'x86_64/libbar.so',
            '/usr/lib/libbaz.so',
            '/opt/lib/libqux.so',
        ]);

        self::assertSame(['/usr/lib', '/opt/lib'], $this->readDirectories($contents));
    }

    public function testCompatCacheIsRead(): void
    {
        $contents = CacheBuilder::createGlibcCache(['/usr/lib/libfoo.so'], "ld.so-1.7.0\0\0\0\0\0");

        self::assertSame(['/usr/lib'], $this->readDirectories($contents));
    }

    public function testLegacyCacheIsRead(): void
    {
        $contents = CacheBuilder::createLegacyCache([
            'libfoo.so.1',
            '/usr/lib/libfoo.so.1',
            'libbar.so',
            '/opt/lib/libbar.so',
        ]);

        self::assertSame(['/usr/lib', '/opt/lib'], $this->readDirectories($contents));
    }

    public function testFirstSupportingFormatIsUsed(): void
    {
        $first = new class implements FormatInterface {
            public function supports(Buffer $buffer): bool
            {
                return true;
            }

            public function getLibraries(Buffer $buffer): iterable
            {
                return ['/first/libfoo.so'];
            }
        };

        $contents = CacheBuilder::createGlibcCache(['/usr/lib/libfoo.so']);

        self::assertSame(['/first'], $this->readDirectories($contents, [$first]));
    }

    public function testTruncatedCacheDoesNotFail(): void
    {
        $caches = [
            CacheBuilder::createGlibcCache(['/usr/lib/libfoo.so', '/opt/lib/libbar.so']),
            CacheBuilder::createLegacyCache(['/usr/lib/libfoo.so']),
        ];

        foreach ($caches as $contents) {
            foreach ([5, 14, 24, 60, 80, \strlen($contents) - 5] as $length) {
                self::assertIsArray($this->readDirectories(\substr($contents, 0, $length)));
            }
        }
    }

    public function testCorruptedEntryCountDoesNotFail(): void
    {
        $contents = CacheBuilder::createGlibcCache(['/usr/lib/libfoo.so']);

        $corrupted = \substr_replace($contents, \pack('L', 0x7FFFFFFF), 20, 4);

        self::assertSame(['/usr/lib'], $this->readDirectories($corrupted));
    }
}
