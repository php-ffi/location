<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory;

/**
 * The directory from which the application (PHP binary) is loaded.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver
 */
final class ApplicationDirectory implements DirectoryProviderInterface
{
    public function getDirectories(): iterable
    {
        yield \dirname(\PHP_BINARY);
    }
}
