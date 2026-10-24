<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Naming;

/**
 * Maps the name of the library to the files of the platform.
 *
 * @internal this is an internal library interface, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver
 */
interface NamingInterface
{
    /**
     * A name that contains a directory separator is a path, it is never searched in the directories.
     */
    public function isPath(string $name): bool;

    /**
     * Returns file names to look for, or pathnames if the name is a path.
     *
     * @param non-empty-string $name
     * @return list<non-empty-string>
     */
    public function getFiles(string $name): array;
}
