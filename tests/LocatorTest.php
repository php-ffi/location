<?php

declare(strict_types=1);

namespace FFI\Location\Tests;

use FFI\Location\Locator;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Locator::class)]
final class LocatorTest extends TestCase
{
    public function testEmptyNameIsNotFound(): void
    {
        self::assertNull(Locator::pathname(''));
        self::assertFalse(Locator::exists(''));
    }

    public function testNameWithNullByteIsNotFound(): void
    {
        self::assertNull(Locator::pathname("lib\0.so"));
    }

    public function testMissingLibraryIsNotFound(): void
    {
        $name = $this->createUniqueName();

        self::assertNull(Locator::pathname($name));
        self::assertFalse(Locator::exists($name));
        self::assertNull(Locator::resolve($name));
    }

    public function testResolvingWithoutLibrariesGivesNull(): void
    {
        self::assertNull(Locator::resolve());
    }

    public function testAbsolutePathnameIsReturned(): void
    {
        $file = $this->createFile($this->createDirectory() . \DIRECTORY_SEPARATOR . $this->createUniqueName());

        self::assertSame($file, Locator::pathname($file));
        self::assertTrue(Locator::exists($file));
    }

    public function testLibraryIsFoundInCurrentDirectory(): void
    {
        $directory = $this->createDirectory();
        $name = $this->createUniqueName();
        $file = $this->createFile($directory . \DIRECTORY_SEPARATOR . $name);

        \chdir($directory);

        self::assertSame($file, Locator::pathname($name));
        self::assertTrue(Locator::exists($name));
    }

    public function testFirstExistingLibraryIsResolved(): void
    {
        $directory = $this->createDirectory();
        $first = $this->createUniqueName();
        $second = $this->createUniqueName();
        $file = $this->createFile($directory . \DIRECTORY_SEPARATOR . $second);

        \chdir($directory);

        self::assertSame($file, Locator::resolve($this->createUniqueName(), $second, $first));
    }

    public function testDirectoryIsNotALibrary(): void
    {
        self::assertNull(Locator::pathname($this->createDirectory()));
    }

    public function testNameWithDirectorySeparatorIsNotSearched(): void
    {
        $directory = $this->createDirectory();
        $name = $this->createUniqueName();
        $this->createFile($directory . '/nested/' . $name);

        \chdir($directory);

        self::assertNotNull(Locator::pathname('nested/' . $name));

        \chdir($this->createDirectory());

        self::assertNull(Locator::pathname('nested/' . $name));
    }

    public function testDllExtensionIsOptionalOnWindows(): void
    {
        if (\PHP_OS_FAMILY !== 'Windows') {
            self::markTestSkipped('Windows only.');
        }

        $directory = $this->createDirectory();
        $base = 'ffi-location-' . \bin2hex(\random_bytes(4));
        $file = $this->createFile($directory . \DIRECTORY_SEPARATOR . $base . '.dll');

        \chdir($directory);

        self::assertSame($file, Locator::pathname($base));
    }
}
