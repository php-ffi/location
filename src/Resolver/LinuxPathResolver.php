<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Location\Resolver;

/**
 * @internal LinuxPathResolver is an internal library class, please do not use it in your code.
 * @psalm-internal FFI\Location
 */
final class LinuxPathResolver extends UnixAwareResolver
{
    /**
     * {@inheritDoc}
     */
    protected function getLibDirectories(): iterable
    {
        yield \getcwd() ?: '.';
        yield from $this->getEnvDirectories('LD_LIBRARY_PATH');
        yield from $this->getLinkerDirectories();
    }
}
