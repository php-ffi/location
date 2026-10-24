<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory;

/**
 * @internal this is an internal library interface, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver
 */
interface DirectoryProviderInterface
{
    /**
     * @return iterable<array-key, string>
     */
    public function getDirectories(): iterable;
}
