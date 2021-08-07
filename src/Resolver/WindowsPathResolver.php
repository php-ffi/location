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
