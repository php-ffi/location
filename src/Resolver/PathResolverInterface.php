<?php

declare(strict_types=1);

namespace FFI\Location\Resolver;

/**
 * @internal this is an internal library interface, please do not use it in your code
 * @psalm-internal FFI\Location
 */
interface PathResolverInterface
{
    /**
     * Returns the real pathname of the library, or `null` if it is not found.
     *
     * @param non-empty-string $name
     * @return non-empty-string|null
     */
    public function resolve(string $name): ?string;
}
