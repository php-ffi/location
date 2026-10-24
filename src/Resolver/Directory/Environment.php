<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver\Directory
 */
final class Environment
{
    /**
     * Returns the value of the environment variable, or `null` if it is unset or empty.
     *
     * @param non-empty-string $name
     */
    public static function findValue(string $name): ?string
    {
        $value = \getenv($name);

        return \is_string($value) && $value !== '' ? $value : null;
    }
}
