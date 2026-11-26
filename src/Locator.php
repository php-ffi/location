<?php

declare(strict_types=1);

namespace FFI\Location;

use FFI\Location\Resolver\LinuxPathResolver;
use FFI\Location\Resolver\MacOSPathResolver;
use FFI\Location\Resolver\PathResolver;
use FFI\Location\Resolver\WindowsPathResolver;

/**
 * Locates shared libraries in the search order of the dynamic linker used by `FFI::cdef()`.
 */
final class Locator
{
    private static ?PathResolver $resolver = null;

    private static function getResolver(): PathResolver
    {
        return self::$resolver ??= match (\PHP_OS_FAMILY) {
            'Windows' => new WindowsPathResolver(),
            'Darwin' => new MacOSPathResolver(),
            default => new LinuxPathResolver(),
        };
    }

    /**
     * Tells whether the library of the given bitness is found.
     */
    public static function exists(string $name, Bitness $bitness = Bitness::Auto): bool
    {
        return self::pathname($name, $bitness) !== null;
    }

    /**
     * Returns the absolute pathname of the library of the given bitness, or
     * {@see null} if it is not found.
     *
     * @return non-empty-string|null
     */
    public static function pathname(string $name, Bitness $bitness = Bitness::Auto): ?string
    {
        return self::getResolver()
            ->resolve($name, $bitness);
    }

    /**
     * Returns the pathname of the first found library of the bitness of the PHP
     * process, or {@see null} if none is found.
     *
     * @return non-empty-string|null
     */
    public static function resolve(string ...$libraries): ?string
    {
        foreach ($libraries as $library) {
            $pathname = self::pathname($library);

            if ($pathname !== null) {
                return $pathname;
            }
        }

        return null;
    }
}
