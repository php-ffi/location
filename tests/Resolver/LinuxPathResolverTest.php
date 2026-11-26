<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver;

use FFI\Location\Bitness;
use FFI\Location\Resolver\LinuxPathResolver;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LinuxPathResolver::class)]
final class LinuxPathResolverTest extends TestCase
{
    protected function setUp(): void
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('Unix paths: a drive letter cannot be used in a list separated by colons');
        }

        parent::setUp();
    }

    public function testLibraryPathIsSearchedInOrder(): void
    {
        [$first, $second] = [$this->createDirectory(), $this->createDirectory()];
        $this->createFile($first . '/libx.so', self::getNativeElf());
        $this->createFile($second . '/libx.so', self::getNativeElf());

        $this->setEnvironment('LD_LIBRARY_PATH', $second . ':' . $first);
        self::assertSame($second . '/libx.so', (new LinuxPathResolver())->resolve('libx.so', Bitness::Auto));

        // The glibc accepts a semicolon as well
        $this->setEnvironment('LD_LIBRARY_PATH', $first . ';' . $second);
        self::assertSame($first . '/libx.so', (new LinuxPathResolver())->resolve('libx.so', Bitness::Auto));
    }

    public function testFilesThatAreNotLibrariesOfTheProcessAreSkipped(): void
    {
        [$script, $foreign, $native] = [$this->createDirectory(), $this->createDirectory(), $this->createDirectory()];
        $this->createFile($script . '/libx.so', "/* GNU ld script */\nGROUP ( libx.so.1 )\n");
        $this->createFile($foreign . '/libx.so', self::getElf(\PHP_INT_SIZE === 8 ? 1 : 2));
        $this->createFile($native . '/libx.so', self::getNativeElf());

        $this->setEnvironment('LD_LIBRARY_PATH', \implode(':', [$script, $foreign, $native]));

        self::assertSame($native . '/libx.so', (new LinuxPathResolver())->resolve('libx.so', Bitness::Auto));
    }

    public function testBitnessSelectsTheLibrary(): void
    {
        [$bits32, $bits64] = [$this->createDirectory(), $this->createDirectory()];
        $this->createFile($bits32 . '/libx.so', self::getElf(1));
        $this->createFile($bits64 . '/libx.so', self::getElf(2));

        $this->setEnvironment('LD_LIBRARY_PATH', $bits32 . ':' . $bits64);
        $resolver = new LinuxPathResolver();

        self::assertSame($bits32 . '/libx.so', $resolver->resolve('libx.so', Bitness::Bits32));
        self::assertSame($bits64 . '/libx.so', $resolver->resolve('libx.so', Bitness::Bits64));
    }

    public function testWorkingDirectoryIsNotSearched(): void
    {
        $directory = $this->createDirectory();
        $name = 'libplanted-' . \bin2hex(\random_bytes(4)) . '.so';
        $this->createFile($directory . '/' . $name, self::getNativeElf());

        $this->setEnvironment('LD_LIBRARY_PATH', null);
        \chdir($directory);

        self::assertNull((new LinuxPathResolver())->resolve($name, Bitness::Auto));
    }

    public function testPathnameIsUsedAsIs(): void
    {
        $directory = $this->createDirectory();
        $library = $this->createFile($directory . '/libx.so', self::getNativeElf());

        self::assertSame($library, (new LinuxPathResolver())->resolve($library, Bitness::Auto));

        \chdir($directory);
        self::assertStringEndsWith('/./libx.so', (string) (new LinuxPathResolver())->resolve('./libx.so', Bitness::Auto));
    }

    public function testLibraryIsFoundInLinkerCache(): void
    {
        if (\PHP_OS_FAMILY !== 'Linux' || !\is_file('/etc/ld.so.cache')) {
            self::markTestSkipped('Requires the glibc linker cache');
        }

        $this->setEnvironment('LD_LIBRARY_PATH', null);

        self::assertNotNull((new LinuxPathResolver())->resolve('libc.so.6', Bitness::Auto));
    }
}
