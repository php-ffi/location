<?php

declare(strict_types=1);

namespace FFI\Location\Resolver;

/**
 * @internal pathResolverInterface is an internal library interface, please do not use it in your code
 * @psalm-internal FFI\Location
 */
interface PathResolverInterface
{
    public function resolve(string $name): ?string;
}
