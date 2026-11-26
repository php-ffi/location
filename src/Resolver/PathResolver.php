<?php

declare(strict_types=1);

namespace FFI\Location\Resolver;

use FFI\Location\Bitness;

/**
 * Searches a library by its name in the directories of the operating system.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location
 */
abstract class PathResolver
{
    /**
     * Separator of the directory and the file name in the returned pathnames.
     *
     * @var non-empty-string
     */
    protected const DIRECTORY_SEPARATOR = '/';

    /**
     * @return non-empty-string|null
     */
    public function resolve(string $name, Bitness $bitness): ?string
    {
        // A name cannot be empty or contain a null byte
        if ($name === '' || \str_contains($name, "\0")) {
            return null;
        }

        $name = $this->getFileName($name);

        if ($name === '') {
            return null;
        }

        if ($this->isPathname($name)) {
            return $this->isLibrary($name, $bitness) ? $this->getAbsolutePathname($name) : null;
        }

        foreach ($this->getDirectories($name) as $directory) {
            $pathname = \rtrim($directory, '/\\') . static::DIRECTORY_SEPARATOR . $name;

            if ($this->isLibrary($pathname, $bitness)) {
                return $pathname;
            }
        }

        return null;
    }

    /**
     * Returns the directories to search the file in, in the search order.
     *
     * @param non-empty-string $file
     * @return iterable<array-key, non-empty-string>
     */
    abstract protected function getDirectories(string $file): iterable;

    /**
     * Returns the name of the file the linker looks for.
     */
    protected function getFileName(string $name): string
    {
        return $name;
    }

    /**
     * Tells whether the name is a pathname that is used as is, without a search.
     */
    protected function isPathname(string $name): bool
    {
        return \str_contains($name, '/');
    }

    /**
     * Tells whether the file is a library of the operating system of the given bitness.
     */
    private function isLibrary(string $pathname, Bitness $bitness): bool
    {
        if (!\is_file($pathname)) {
            return false;
        }

        foreach ($this->getBitnesses($pathname) as $is64Bit) {
            if ($bitness->accepts($is64Bit)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether the code in the library is 64-bit, one value per architecture
     * of the file, or an empty list if the file is not a library of the operating system.
     *
     * @return list<bool>
     */
    abstract protected function getBitnesses(string $pathname): array;

    /**
     * Reads the beginning of the file, an empty string if it cannot be read.
     *
     * @param int<1, max> $length
     */
    protected static function readHeader(string $pathname, int $length): string
    {
        $header = @\file_get_contents($pathname, false, null, 0, $length);

        return \is_string($header) ? $header : '';
    }

    /**
     * @param non-empty-string $pathname
     * @return non-empty-string
     */
    protected function getAbsolutePathname(string $pathname): string
    {
        if ($pathname[0] === '/') {
            return $pathname;
        }

        $directory = \getcwd();

        return $directory === false ? $pathname : $directory . static::DIRECTORY_SEPARATOR . $pathname;
    }

    /**
     * Returns the non-empty entries of the list of directories in the environment variable.
     *
     * @param non-empty-string $variable
     * @param non-empty-string $delimiters
     * @return list<non-empty-string>
     */
    protected static function getEnvironmentDirectories(string $variable, string $delimiters): array
    {
        // The process environment, not the variables of the web server under FPM and CGI
        $value = \getenv($variable, true);

        if (!\is_string($value) || $value === '') {
            return [];
        }

        $directories = \preg_split('/[' . \preg_quote($delimiters, '/') . ']/', $value, -1, \PREG_SPLIT_NO_EMPTY);

        /** @var list<non-empty-string> */
        return $directories === false ? [] : $directories;
    }
}
