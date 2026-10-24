<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver
 */
final class FixedDirectories implements DirectoryProviderInterface
{
    /**
     * @readonly
     *
     * @var list<string>
     */
    private array $directories;

    public function __construct(string ...$directories)
    {
        $this->directories = \array_values($directories);
    }

    public function getDirectories(): iterable
    {
        return $this->directories;
    }
}
