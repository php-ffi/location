<?php

declare(strict_types=1);

namespace FFI\Location\Resolver;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location
 */
final class MacOSPathResolver extends UnixAwareResolver
{
    protected function getLibDirectories(): iterable
    {
        // Allow non-strict short ternary operator
        // @phpstan-ignore ternary.shortNotAllowed
        yield \getcwd() ?: '.';
        yield from $this->getEnvDirectories('DYLD_LIBRARY_PATH');
        yield from $this->getEnvDirectories('DYLD_FALLBACK_LIBRARY_PATH');
        yield from $this->getLinkerDirectories();
    }
}
