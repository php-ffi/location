<?php

declare(strict_types=1);

namespace FFI\Location\Resolver;

/**
 * Search order of `LoadLibrary` in the safe DLL search mode.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location
 */
final class WindowsPathResolver extends PathResolver
{
    protected const DIRECTORY_SEPARATOR = '\\';

    /**
     * Values of the `Machine` field of the PE header: x86, ARM Thumb-2, x86-64, ARM64.
     */
    private const MACHINE_I386 = 0x014C;

    private const MACHINE_ARMNT = 0x01C4;

    private const MACHINE_AMD64 = 0x8664;

    private const MACHINE_ARM64 = 0xAA64;

    protected function getDirectories(string $file): iterable
    {
        // @phpstan-ignore-next-line notIdentical.alwaysTrue, PHP_BINARY is empty under a web server module
        if (\PHP_BINARY !== '') {
            yield \dirname(\PHP_BINARY);
        }

        $root = \getenv('SystemRoot', true);

        if (\is_string($root) && $root !== '') {
            yield $root . '\\System32';
            yield $root . '\\System';
            yield $root;
        }

        $directory = \getcwd();

        if ($directory !== false) {
            yield $directory;
        }

        yield from self::getEnvironmentDirectories('PATH', ';');
    }

    /**
     * Appends `.dll` to a name without an extension, a trailing dot disables it.
     */
    protected function getFileName(string $name): string
    {
        if (\str_ends_with($name, '.')) {
            return \substr($name, 0, -1);
        }

        $basename = \basename(\str_replace('\\', '/', $name));

        return \str_contains($basename, '.') ? $name : $name . '.dll';
    }

    protected function isPathname(string $name): bool
    {
        return \strpbrk($name, '/\\:') !== false;
    }

    /**
     * The `Machine` field of the PE header.
     */
    protected function getBitnesses(string $pathname): array
    {
        $header = self::readHeader($pathname, 4096);

        if (\strlen($header) < 64 || !\str_starts_with($header, 'MZ')) {
            return [];
        }

        /** @var array{1: int} $offset */
        $offset = \unpack('V', $header, 0x3C);

        if ($offset[1] + 6 > \strlen($header) || \substr($header, $offset[1], 4) !== "PE\0\0") {
            return [];
        }

        /** @var array{1: int} $machine */
        $machine = \unpack('v', $header, $offset[1] + 4);

        return match ($machine[1]) {
            self::MACHINE_I386, self::MACHINE_ARMNT => [false],
            self::MACHINE_AMD64, self::MACHINE_ARM64 => [true],
            default => [],
        };
    }

    protected function getAbsolutePathname(string $pathname): string
    {
        $pathname = \str_replace('/', '\\', $pathname);
        $absolute = \realpath($pathname);

        return $absolute === false ? $pathname : $absolute;
    }
}
