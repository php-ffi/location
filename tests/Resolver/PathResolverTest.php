<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver;

use FFI\Location\Resolver\Directory\FixedDirectories;
use FFI\Location\Resolver\Naming\UnixNaming;
use FFI\Location\Resolver\Naming\WindowsNaming;
use FFI\Location\Resolver\PathResolver;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathResolver::class)]
final class PathResolverTest extends TestCase
{
    public function testDirectoriesAreSearchedInOrder(): void
    {
        $first = $this->createDirectory();
        $second = $this->createDirectory();
        $name = $this->createUniqueName();
        $this->createFile($second . '/' . $name);
        $firstFile = $this->createFile($first . '/' . $name);

        $resolver = new PathResolver(new UnixNaming(), new FixedDirectories($first, $second));

        self::assertSame($firstFile, $resolver->resolve($name));
    }

    public function testProvidersAreSearchedInOrder(): void
    {
        $first = $this->createDirectory();
        $second = $this->createDirectory();
        $name = $this->createUniqueName();
        $this->createFile($second . '/' . $name);
        $firstFile = $this->createFile($first . '/' . $name);

        $resolver = new PathResolver(
            new UnixNaming(),
            new FixedDirectories('/non/existent'),
            new FixedDirectories($first),
            new FixedDirectories($second),
        );

        self::assertSame($firstFile, $resolver->resolve($name));
    }

    public function testMissingLibraryIsNotFound(): void
    {
        $resolver = new PathResolver(new UnixNaming(), new FixedDirectories($this->createDirectory()));

        self::assertNull($resolver->resolve($this->createUniqueName()));
    }

    public function testDirectoryIsNotALibrary(): void
    {
        $directory = $this->createDirectory();
        $this->createFile($directory . '/nested/' . $this->createUniqueName());

        $resolver = new PathResolver(new UnixNaming(), new FixedDirectories($directory));

        self::assertNull($resolver->resolve('nested'));
    }

    public function testEmptyDirectoryDoesNotPointToTheRoot(): void
    {
        $resolver = new PathResolver(new UnixNaming(), new FixedDirectories(''));

        self::assertNull($resolver->resolve('bin'));
    }

    public function testLibraryInstalledLaterIsFound(): void
    {
        $directory = $this->createDirectory();
        $name = $this->createUniqueName();

        $resolver = new PathResolver(new UnixNaming(), new FixedDirectories($directory));

        self::assertNull($resolver->resolve($name));

        $file = $this->createFile($directory . '/' . $name);

        self::assertSame($file, $resolver->resolve($name));
    }

    public function testPathIsNotSearchedInDirectories(): void
    {
        $directory = $this->createDirectory();
        $name = $this->createUniqueName();
        $this->createFile($directory . '/nested/' . $name);

        $resolver = new PathResolver(new UnixNaming(), new FixedDirectories($directory));

        self::assertNull($resolver->resolve('nested/' . $name));

        \chdir($directory);

        self::assertNotNull($resolver->resolve('nested/' . $name));
    }

    public function testAbsolutePathIsResolved(): void
    {
        $this->skipOnWindows();

        $file = $this->createFile($this->createDirectory() . '/' . $this->createUniqueName());

        self::assertSame($file, (new PathResolver(new UnixNaming()))->resolve($file));
    }

    public function testAllFileNamesAreTriedInEachDirectory(): void
    {
        $first = $this->createDirectory();
        $second = $this->createDirectory();
        $base = 'ffi-location-' . \bin2hex(\random_bytes(4));
        $file = $this->createFile($first . '/' . $base . '.dll');
        $this->createFile($second . '/' . $base . '.dll');

        $resolver = new PathResolver(new WindowsNaming(), new FixedDirectories($first, $second));

        self::assertSame($file, $resolver->resolve($base));
    }

    public function testPathWithoutExtensionIsResolvedWithWindowsNaming(): void
    {
        $directory = $this->createDirectory();
        $base = 'ffi-location-' . \bin2hex(\random_bytes(4));
        $file = $this->createFile($directory . '/' . $base . '.dll');

        $resolver = new PathResolver(new WindowsNaming());

        self::assertSame($file, $resolver->resolve($directory . '/' . $base));
        self::assertSame($file, $resolver->resolve($directory . '/' . $base . '.dll'));
    }

    public function testSymlinkIsResolvedToItsTarget(): void
    {
        $this->skipOnWindows();

        $directory = $this->createDirectory();
        $real = $this->createFile($directory . '/' . $this->createUniqueName('.so.1.2'));
        $link = $directory . '/' . $this->createUniqueName('.so');

        if (!@\symlink($real, $link)) {
            self::markTestSkipped('Symlinks are not available.');
        }

        $this->trackFile($link);

        $resolver = new PathResolver(new UnixNaming(), new FixedDirectories($directory));

        self::assertSame($real, $resolver->resolve(\basename($link)));
    }
}
