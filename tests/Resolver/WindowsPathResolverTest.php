<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver;

use FFI\Location\Bitness;
use FFI\Location\Resolver\WindowsPathResolver;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WindowsPathResolver::class)]
final class WindowsPathResolverTest extends TestCase
{
    protected function setUp(): void
    {
        if (\PHP_OS_FAMILY !== 'Windows') {
            self::markTestSkipped('Windows paths');
        }

        parent::setUp();
    }

    public function testDllIsAppendedToNameWithoutExtension(): void
    {
        $resolver = new WindowsPathResolver();

        self::assertSame($resolver->resolve('kernel32.dll', Bitness::Auto), $resolver->resolve('kernel32', Bitness::Auto));
        self::assertNull($resolver->resolve('kernel32.', Bitness::Auto));
    }

    public function testWorkingDirectoryIsSearchedBeforePath(): void
    {
        [$working, $path] = [$this->createDirectory(), $this->createDirectory()];
        $name = 'x' . \bin2hex(\random_bytes(4));
        $this->createFile($working . '\\' . $name . '.dll', self::getNativePe());
        $this->createFile($path . '\\' . $name . '.dll', self::getNativePe());

        $this->setEnvironment('PATH', $path);
        \chdir($working);

        self::assertSame($working . '\\' . $name . '.dll', (new WindowsPathResolver())->resolve($name, Bitness::Auto));
    }

    public function testDllOfAnotherArchitectureIsSkipped(): void
    {
        [$foreign, $native] = [$this->createDirectory(), $this->createDirectory()];
        $name = 'x' . \bin2hex(\random_bytes(4));
        $this->createFile($foreign . '\\' . $name . '.dll', self::getPe(\PHP_INT_SIZE === 8 ? 0x014C : 0x8664));
        $this->createFile($native . '\\' . $name . '.dll', self::getNativePe());

        $this->setEnvironment('PATH', $foreign . ';' . $native);

        self::assertSame($native . '\\' . $name . '.dll', (new WindowsPathResolver())->resolve($name, Bitness::Auto));
    }

    public function testBitnessSelectsTheDll(): void
    {
        [$x86, $x64] = [$this->createDirectory(), $this->createDirectory()];
        $name = 'x' . \bin2hex(\random_bytes(4));
        $this->createFile($x86 . '\\' . $name . '.dll', self::getPe(0x014C));
        $this->createFile($x64 . '\\' . $name . '.dll', self::getPe(0x8664));

        $this->setEnvironment('PATH', $x86 . ';' . $x64);
        $resolver = new WindowsPathResolver();

        self::assertSame($x86 . '\\' . $name . '.dll', $resolver->resolve($name, Bitness::Bits32));
        self::assertSame($x64 . '\\' . $name . '.dll', $resolver->resolve($name, Bitness::Bits64));
    }
}
