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
 * @internal PathResolverInterface is an internal library interface, please do not use it in your code.
 * @psalm-internal FFI\Location
 */
interface PathResolverInterface
{
    /**
     * @param string $name
     * @return string|null
     */
    public function resolve(string $name): ?string;
}
