<?php

declare(strict_types=1);

namespace FFI\Location\Resolver;

/**
 * @internal WindowsPathResolver is an internal library class, please do not use it in your code.
 * @psalm-internal FFI\Location
 */
final class WindowsPathResolver extends PathResolver
{
    /**
     * {@inheritDoc}
     */
    protected function getLibDirectories(): iterable
    {
        yield \getcwd() ?: '.';
        yield from $this->getEnvDirectories('PATH', ';');
    }
}
