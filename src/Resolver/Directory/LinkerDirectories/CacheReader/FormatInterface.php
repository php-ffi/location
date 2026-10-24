<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader;

/**
 * @internal this is an internal library interface, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader
 */
interface FormatInterface
{
    /**
     * Returns pathnames of the libraries listed in the cache, or `null` if the cache is of another format.
     *
     * @return iterable<array-key, string>|null
     */
    public function findLibraries(Buffer $buffer): ?iterable;
}
