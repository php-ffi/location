<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Naming;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver
 */
final class UnixNaming implements NamingInterface
{
    public function isPath(string $name): bool
    {
        return \strpos($name, '/') !== false;
    }

    public function getFiles(string $name): array
    {
        return [$name];
    }
}
