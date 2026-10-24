<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Naming;

/**
 * Appends the `.dll` extension to a name without extension, a trailing dot suppresses it.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver
 */
final class WindowsNaming implements NamingInterface
{
    public function isPath(string $name): bool
    {
        // A slash, a backslash or a drive, like `C:name.dll`
        return \strpbrk($name, '/\\:') !== false;
    }

    public function getFiles(string $name): array
    {
        if (\substr($name, -1) === '.') {
            $trimmed = \substr($name, 0, -1);

            return $trimmed === '' ? [] : [$trimmed];
        }

        $basename = \basename(\str_replace('\\', '/', $name));

        return [\strpos($basename, '.') === false ? $name . '.dll' : $name];
    }
}
