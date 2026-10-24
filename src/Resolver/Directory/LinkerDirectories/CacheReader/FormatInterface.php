<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader;

/**
 * @internal this is an internal library interface, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader
 */
interface FormatInterface
{
    public function supports(Buffer $buffer): bool;

    /**
     * @return iterable<array-key, string>
     */
    public function getLibraries(Buffer $buffer): iterable;
}
