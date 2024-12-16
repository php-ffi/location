<?php

declare(strict_types=1);

namespace FFI\Location\Resolver;

/**
 * @internal linuxPathResolver is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location
 */
final class LinuxPathResolver extends UnixAwareResolver
{
    protected function getLibDirectories(): iterable
    {
        yield \getcwd() ?: '.';
        yield from $this->getEnvDirectories('LD_LIBRARY_PATH');
        yield from $this->getLinkerDirectories();
    }
}
