<?php

declare(strict_types=1);

namespace FFI\Location;

use FFI\Location\Resolver\PathResolverFactory;
use FFI\Location\Resolver\PathResolverInterface;

/**
 * Locates binary libraries in the order of the dynamic linker used by `FFI::cdef()`.
 */
final class Locator
{
    private static ?PathResolverInterface $resolver = null;

    private static function getResolver(): PathResolverInterface
    {
        return self::$resolver
            ??= PathResolverFactory::create(\PHP_OS_FAMILY);
    }

    public static function exists(string $name): bool
    {
        return self::pathname($name) !== null;
    }

    /**
     * Returns the real pathname of the library, or `null` if it is not found.
     *
     * @return non-empty-string|null
     */
    public static function pathname(string $name): ?string
    {
        // A name cannot be empty or contain a null byte
        if ($name === '' || \strpos($name, "\0") !== false) {
            return null;
        }

        return self::getResolver()
            ->resolve($name);
    }

    /**
     * Returns the pathname of the first found library, or `null` if none is found.
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
