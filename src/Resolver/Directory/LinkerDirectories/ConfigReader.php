<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory\LinkerDirectories;

/**
 * Reads library directories from the linker configuration, like `ld.so.conf`.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver
 */
final class ConfigReader implements ReaderInterface
{
    /**
     * @readonly
     */
    private string $pathname;

    public function __construct(string $pathname)
    {
        $this->pathname = $pathname;
    }

    /**
     * @return \Generator<array-key, string>
     */
    public function getIterator(): \Generator
    {
        return $this->read($this->pathname, []);
    }

    /**
     * @param array<string, true> $visited configs that are already read
     * @return \Generator<array-key, non-empty-string>
     */
    private function read(string $pathname, array $visited): \Generator
    {
        $real = \realpath($pathname);

        if ($real === false || isset($visited[$real])) {
            return;
        }

        $visited[$real] = true;

        foreach ($this->readLines($real) as $line) {
            if (\preg_match('/^include\s+(.+)$/', $line, $matches) === 1) {
                yield from $this->readIncludes($matches[1], \dirname($real), $visited);

                continue;
            }

            yield from $this->readDirectories($line);
        }
    }

    /**
     * @return \Generator<array-key, non-empty-string>
     */
    private function readLines(string $pathname): \Generator
    {
        $fp = \is_file($pathname) ? @\fopen($pathname, 'rb') : false;

        if ($fp === false) {
            return;
        }

        try {
            while (($line = \fgets($fp)) !== false) {
                $line = \trim(\explode('#', $line, 2)[0]);

                if ($line !== '') {
                    yield $line;
                }
            }
        } finally {
            \fclose($fp);
        }
    }

    /**
     * Directories are separated by colons, spaces, tabs, newlines or commas.
     *
     * @return \Generator<array-key, non-empty-string>
     */
    private function readDirectories(string $line): \Generator
    {
        $directories = \preg_split('/[:,\s]+/', $line, -1, \PREG_SPLIT_NO_EMPTY);

        if ($directories === false) {
            return;
        }

        foreach ($directories as $directory) {
            if ($directory[0] === '/') {
                yield $directory;
            }
        }
    }

    /**
     * Relative includes are resolved against the directory of the including config.
     *
     * @param array<string, true> $visited
     * @return \Generator<array-key, non-empty-string>
     */
    private function readIncludes(string $pattern, string $base, array $visited): \Generator
    {
        $configs = \glob($pattern[0] === '/' ? $pattern : $base . '/' . $pattern);

        if ($configs === false) {
            return;
        }

        foreach ($configs as $config) {
            yield from $this->read($config, $visited);
        }
    }
}
