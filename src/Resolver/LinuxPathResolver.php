<?php

declare(strict_types=1);

namespace FFI\Location\Resolver;

/**
 * Search order of the glibc dynamic linker `ld.so`.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location
 */
final class LinuxPathResolver extends PathResolver
{
    /**
     * @var non-empty-string
     */
    private const LINKER_CACHE = '/etc/ld.so.cache';

    /**
     * Upper bound of the read part of the linker cache, a real one is about 100 KiB.
     */
    private const MAX_CACHE_SIZE = 16 * 1024 * 1024;

    /**
     * Default directories of the linker, searched after the linker cache.
     *
     * @var list<non-empty-string>
     */
    private const DEFAULT_DIRECTORIES = ['/lib64', '/usr/lib64', '/lib', '/usr/lib'];

    /**
     * @var array<non-empty-string, list<non-empty-string>>|null
     */
    private ?array $cache = null;

    protected function getDirectories(string $file): iterable
    {
        // The glibc accepts a colon or a semicolon as a delimiter
        yield from self::getEnvironmentDirectories('LD_LIBRARY_PATH', ':;');
        yield from $this->getCacheDirectories($file);
        yield from self::DEFAULT_DIRECTORIES;
    }

    /**
     * The class of an ELF object, `ELFCLASS32` or `ELFCLASS64`.
     */
    protected function getBitnesses(string $pathname): array
    {
        $header = self::readHeader($pathname, 5);

        if (\strlen($header) !== 5 || !\str_starts_with($header, "\x7FELF")) {
            return [];
        }

        return match (\ord($header[4])) {
            1 => [false],
            2 => [true],
            default => [],
        };
    }

    /**
     * Returns the directories of the file registered in the linker cache under the name.
     *
     * @param non-empty-string $file
     * @return list<non-empty-string>
     */
    private function getCacheDirectories(string $file): array
    {
        if ($this->cache === null) {
            $this->cache = self::readCache();
        }

        return $this->cache[$file] ?? [];
    }

    /**
     * Reads the pathnames of the linker cache, grouped by the file name.
     *
     * The pathnames are taken like the `strings` utility does, as the runs of printable
     * characters that start with a slash, so every version of the cache format is read.
     *
     * @return array<non-empty-string, list<non-empty-string>>
     */
    private static function readCache(): array
    {
        $contents = @\file_get_contents(self::LINKER_CACHE, false, null, 0, self::MAX_CACHE_SIZE);

        if (!\is_string($contents)) {
            return [];
        }

        // At least 5 characters, like `strings -n5`
        \preg_match_all('#/[\x20-\x7E]{4,}#', $contents, $matches);

        $result = [];

        foreach ($matches[0] as $pathname) {
            /** @var non-empty-string $directory the pathname starts with a slash */
            $directory = \dirname($pathname);
            $file = \basename($pathname);

            if ($file !== '' && !\in_array($directory, $result[$file] ?? [], true)) {
                $result[$file][] = $directory;
            }
        }

        return $result;
    }
}
