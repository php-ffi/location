<?php

declare(strict_types=1);

namespace FFI\Location\Resolver;

/**
 * Search order of the macOS dynamic linker `dyld`.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location
 */
final class MacOSPathResolver extends PathResolver
{
    /**
     * Directories searched when `DYLD_FALLBACK_LIBRARY_PATH` is not set.
     *
     * @var list<non-empty-string>
     */
    private const DEFAULT_FALLBACK_DIRECTORIES = ['/usr/local/lib', '/usr/lib'];

    /**
     * Magic numbers of Mach-O files as they are stored on disk.
     */
    private const MAGIC_32 = "\xCE\xFA\xED\xFE";

    private const MAGIC_64 = "\xCF\xFA\xED\xFE";

    /**
     * Magic number of a universal binary, which contains a file per architecture.
     */
    private const MAGIC_FAT = "\xCA\xFE\xBA\xBE";

    /**
     * The flag of 64-bit architectures in the CPU type of a universal binary entry.
     */
    private const CPU_ARCH_ABI64 = 0x0100_0000;

    protected function getDirectories(string $file): iterable
    {
        yield from self::getEnvironmentDirectories('DYLD_LIBRARY_PATH', ':');

        $fallback = self::getEnvironmentDirectories('DYLD_FALLBACK_LIBRARY_PATH', ':');

        yield from $fallback === [] ? self::DEFAULT_FALLBACK_DIRECTORIES : $fallback;
    }

    /**
     * The magic number of a Mach-O file, or the CPU types of the architectures of a
     * universal binary.
     */
    protected function getBitnesses(string $pathname): array
    {
        $header = self::readHeader($pathname, 4096);

        $magic = \substr($header, 0, 4);

        if ($magic !== self::MAGIC_FAT) {
            return match ($magic) {
                self::MAGIC_32 => [false],
                self::MAGIC_64 => [true],
                default => [],
            };
        }

        /** @var array{1: int} $count */
        $count = \unpack('N', $header . "\0\0\0\0", 4);
        $result = [];

        // The header is followed by the 20-byte entries, the CPU type comes first
        for ($i = 0, $offset = 8; $i < $count[1] && $offset + 4 <= \strlen($header); ++$i, $offset += 20) {
            /** @var array{1: int} $type */
            $type = \unpack('N', $header, $offset);
            $result[] = ($type[1] & self::CPU_ARCH_ABI64) !== 0;
        }

        return $result;
    }
}
