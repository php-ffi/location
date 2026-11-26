<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Unit;

use FFI\Location\Locator;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Locator::class)]
final class LocatorTest extends TestCase
{
    public function testSystemLibraryIsFound(): void
    {
        switch (\PHP_OS_FAMILY) {
            case 'Windows':
                $pathname = Locator::pathname('kernel32');

                self::assertNotNull($pathname);
                self::assertStringEndsWithIgnoringCase('\\System32\\kernel32.dll', $pathname);
                break;

            case 'Linux':
                self::assertNotNull(Locator::pathname('libc.so.6'));
                break;

            default:
                self::markTestSkipped('The system libraries of macOS are not files since macOS 11');
        }
    }

    public function testUnknownLibraryIsNotFound(): void
    {
        self::assertNull(Locator::pathname('no-such-library-' . \bin2hex(\random_bytes(4))));
        self::assertFalse(Locator::exists('no-such-library-' . \bin2hex(\random_bytes(4))));
    }

    public function testEmptyNameAndNameWithNullByteAreNotFound(): void
    {
        self::assertNull(Locator::pathname(''));
        self::assertNull(Locator::pathname(__FILE__ . "\0"));
    }

    public function testResolveReturnsTheFirstFoundLibrary(): void
    {
        $directory = $this->createDirectory();
        $library = $this->createFile(
            $directory . \DIRECTORY_SEPARATOR . (\PHP_OS_FAMILY === 'Windows' ? 'b.dll' : 'libb.so'),
            self::getNativeLibrary(),
        );

        self::assertSame($library, Locator::resolve($directory . '/missing', $library));
        self::assertNull(Locator::resolve($directory . '/missing'));
    }

    private static function assertStringEndsWithIgnoringCase(string $suffix, string $actual): void
    {
        self::assertStringEndsWith(\strtolower($suffix), \strtolower($actual));
    }
}
