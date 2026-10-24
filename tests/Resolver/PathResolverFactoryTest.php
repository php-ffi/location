<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver;

use FFI\Location\Resolver\PathResolverFactory;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathResolverFactory::class)]
final class PathResolverFactoryTest extends TestCase
{
    public function testLinuxSearchesLdLibraryPath(): void
    {
        $this->skipOnWindows();

        $directory = $this->createDirectory();
        $name = $this->createUniqueName('.so');
        $file = $this->createFile($directory . '/' . $name);

        $this->setEnv('LD_LIBRARY_PATH', '/non/existent::' . $directory);

        self::assertSame($file, PathResolverFactory::create('Linux')->resolve($name));
    }

    public function testLinuxAcceptsSemicolonAsDelimiter(): void
    {
        $this->skipOnWindows();

        $directory = $this->createDirectory();
        $name = $this->createUniqueName('.so');
        $file = $this->createFile($directory . '/' . $name);

        $this->setEnv('LD_LIBRARY_PATH', '/non/existent;' . $directory);

        self::assertSame($file, PathResolverFactory::create('Linux')->resolve($name));
    }

    public function testLinuxKeepsSpacesInDirectories(): void
    {
        $this->skipOnWindows();

        $directory = $this->createDirectory() . '/with space ';
        $name = $this->createUniqueName('.so');
        $file = $this->createFile($directory . '/' . $name);

        $this->setEnv('LD_LIBRARY_PATH', $directory);

        self::assertSame($file, PathResolverFactory::create('Linux')->resolve($name));
    }

    public function testLinuxSearchesCurrentDirectoryLast(): void
    {
        $this->skipOnWindows();

        $working = $this->createDirectory();
        $linker = $this->createDirectory();
        $name = $this->createUniqueName('.so');
        $this->createFile($working . '/' . $name);
        $linkerFile = $this->createFile($linker . '/' . $name);

        \chdir($working);
        $this->setEnv('LD_LIBRARY_PATH', $linker);

        self::assertSame($linkerFile, PathResolverFactory::create('Linux')->resolve($name));

        $only = $this->createUniqueName('.so');
        $workingFile = $this->createFile($working . '/' . $only);

        self::assertSame($workingFile, PathResolverFactory::create('Linux')->resolve($only));
    }

    public function testLinuxFindsSystemLibrary(): void
    {
        if (\PHP_OS_FAMILY !== 'Linux') {
            self::markTestSkipped('Linux only.');
        }

        $this->setEnv('LD_LIBRARY_PATH', null);

        self::assertNotNull(PathResolverFactory::create('Linux')->resolve('libc.so.6'));
    }

    public function testUnknownFamilyIsResolvedAsLinux(): void
    {
        $this->skipOnWindows();

        $directory = $this->createDirectory();
        $name = $this->createUniqueName('.so');
        $file = $this->createFile($directory . '/' . $name);

        $this->setEnv('LD_LIBRARY_PATH', $directory);

        self::assertSame($file, PathResolverFactory::create('BSD')->resolve($name));
    }

    public function testMacOSSearchesDyldPaths(): void
    {
        $this->skipOnWindows();

        $first = $this->createDirectory();
        $second = $this->createDirectory();
        $firstName = $this->createUniqueName('.dylib');
        $secondName = $this->createUniqueName('.dylib');
        $firstFile = $this->createFile($first . '/' . $firstName);
        $secondFile = $this->createFile($second . '/' . $secondName);

        $this->setEnv('DYLD_LIBRARY_PATH', $first);
        $this->setEnv('DYLD_FALLBACK_LIBRARY_PATH', $second);

        $resolver = PathResolverFactory::create('Darwin');

        self::assertSame($firstFile, $resolver->resolve($firstName));
        self::assertSame($secondFile, $resolver->resolve($secondName));
    }

    public function testWindowsSearchesPathVariable(): void
    {
        $first = $this->createDirectory();
        $second = $this->createDirectory();
        $name = $this->createUniqueName('.dll');
        $file = $this->createFile($second . '/' . $name);

        $this->setEnv('PATH', $first . ';' . $second);

        self::assertSame($file, PathResolverFactory::create('Windows')->resolve($name));
    }

    public function testWindowsSearchesQuotedPathEntries(): void
    {
        $directory = $this->createDirectory();
        $name = $this->createUniqueName('.dll');
        $file = $this->createFile($directory . '/' . $name);

        $this->setEnv('PATH', '"' . $directory . '"');

        self::assertSame($file, PathResolverFactory::create('Windows')->resolve($name));
    }

    public function testWindowsAppendsDllExtensionToNameWithoutExtension(): void
    {
        $directory = $this->createDirectory();
        $base = 'ffi-location-' . \bin2hex(\random_bytes(4));
        $file = $this->createFile($directory . '/' . $base . '.dll');
        $this->createFile($directory . '/' . $base);

        $this->setEnv('PATH', $directory);

        self::assertSame($file, PathResolverFactory::create('Windows')->resolve($base));
    }

    public function testWindowsTrailingDotSuppressesExtension(): void
    {
        $directory = $this->createDirectory();
        $base = 'ffi-location-' . \bin2hex(\random_bytes(4));
        $this->createFile($directory . '/' . $base . '.dll');
        $file = $this->createFile($directory . '/' . $base);

        $this->setEnv('PATH', $directory);

        self::assertSame($file, PathResolverFactory::create('Windows')->resolve($base . '.'));
    }

    public function testWindowsDriveRelativeNameIsNotSearched(): void
    {
        $this->setEnv('PATH', $this->createDirectory());

        self::assertNull(PathResolverFactory::create('Windows')->resolve('C:' . $this->createUniqueName('.dll')));
    }
}
