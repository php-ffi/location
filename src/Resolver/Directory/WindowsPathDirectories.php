<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory;

/**
 * Directories of the `PATH` variable. Entries may be quoted, like `"C:\Program Files\bin"`.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver
 */
final class WindowsPathDirectories implements DirectoryProviderInterface
{
    public function getDirectories(): iterable
    {
        $value = Environment::findValue('PATH');

        if ($value === null) {
            return;
        }

        foreach (\str_getcsv($value, ';', '"', '') as $directory) {
            if ($directory !== null && $directory !== '') {
                yield $directory;
            }
        }
    }
}
