<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver
 */
final class WorkingDirectory implements DirectoryProviderInterface
{
    public function getDirectories(): iterable
    {
        $directory = \getcwd();

        yield $directory === false ? '.' : $directory;
    }
}
