<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver;

use FFI\Location\Bitness;
use FFI\Location\Resolver\MacOSPathResolver;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MacOSPathResolver::class)]
final class MacOSPathResolverTest extends TestCase
{
    protected function setUp(): void
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('Unix paths: a drive letter cannot be used in a list separated by colons');
        }

        parent::setUp();
    }

    public function testLibraryPathIsSearchedBeforeFallbackPath(): void
    {
        [$library, $fallback] = [$this->createDirectory(), $this->createDirectory()];
        $this->createFile($library . '/libx.dylib', self::getNativeMachO());
        $this->createFile($fallback . '/libx.dylib', self::getNativeMachO());
        $this->createFile($fallback . '/liby.dylib', self::getNativeMachO());

        $this->setEnvironment('DYLD_LIBRARY_PATH', $library);
        $this->setEnvironment('DYLD_FALLBACK_LIBRARY_PATH', $fallback);
        $resolver = new MacOSPathResolver();

        self::assertSame($library . '/libx.dylib', $resolver->resolve('libx.dylib', Bitness::Auto));
        self::assertSame($fallback . '/liby.dylib', $resolver->resolve('liby.dylib', Bitness::Auto));
    }

    public function testBitnessSelectsTheLibrary(): void
    {
        [$bits32, $bits64, $universal] = [$this->createDirectory(), $this->createDirectory(), $this->createDirectory()];
        $this->createFile($bits32 . '/libx.dylib', "\xCE\xFA\xED\xFE" . \str_repeat("\0", 28));
        $this->createFile($bits64 . '/libx.dylib', "\xCF\xFA\xED\xFE" . \str_repeat("\0", 28));
        // A universal binary of `x86_64` (0x01000007) and `arm64` (0x0100000C)
        $this->createFile($universal . '/liby.dylib', "\xCA\xFE\xBA\xBE" . \pack('N', 2)
            . \pack('N5', 0x0100_0007, 3, 0x1000, 0x1000, 12)
            . \pack('N5', 0x0100_000C, 0, 0x2000, 0x1000, 14));

        $this->setEnvironment('DYLD_LIBRARY_PATH', $bits32 . ':' . $bits64 . ':' . $universal);
        $resolver = new MacOSPathResolver();

        self::assertSame($bits32 . '/libx.dylib', $resolver->resolve('libx.dylib', Bitness::Bits32));
        self::assertSame($bits64 . '/libx.dylib', $resolver->resolve('libx.dylib', Bitness::Bits64));
        self::assertSame($universal . '/liby.dylib', $resolver->resolve('liby.dylib', Bitness::Bits64));
        self::assertNull($resolver->resolve('liby.dylib', Bitness::Bits32));
    }

    public function testFileThatIsNotMachOIsSkipped(): void
    {
        [$text, $library] = [$this->createDirectory(), $this->createDirectory()];
        $this->createFile($text . '/libx.dylib', 'not a library');
        $this->createFile($library . '/libx.dylib', self::getNativeMachO());

        $this->setEnvironment('DYLD_LIBRARY_PATH', $text . ':' . $library);

        self::assertSame($library . '/libx.dylib', (new MacOSPathResolver())->resolve('libx.dylib', Bitness::Auto));
    }

    public function testWorkingDirectoryIsNotSearched(): void
    {
        $directory = $this->createDirectory();
        $name = 'libplanted-' . \bin2hex(\random_bytes(4)) . '.dylib';
        $this->createFile($directory . '/' . $name, self::getNativeMachO());

        $this->setEnvironment('DYLD_LIBRARY_PATH', null);
        $this->setEnvironment('DYLD_FALLBACK_LIBRARY_PATH', null);
        \chdir($directory);

        self::assertNull((new MacOSPathResolver())->resolve($name, Bitness::Auto));
    }

    /**
     * A Mach-O file of the bitness of the PHP process.
     */
    private static function getNativeMachO(): string
    {
        return (\PHP_INT_SIZE === 8 ? "\xCF\xFA\xED\xFE" : "\xCE\xFA\xED\xFE") . \str_repeat("\0", 28);
    }
}
