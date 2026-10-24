<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory;

/**
 * Subdirectories of the user home directory, like `$HOME/lib`.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver
 */
final class HomeDirectories implements DirectoryProviderInterface
{
    /**
     * @readonly
     *
     * @var list<string>
     */
    private array $subdirectories;

    public function __construct(string ...$subdirectories)
    {
        $this->subdirectories = \array_values($subdirectories);
    }

    public function getDirectories(): iterable
    {
        $home = Environment::findValue('HOME');

        if ($home === null) {
            return;
        }

        foreach ($this->subdirectories as $subdirectory) {
            yield $home . '/' . $subdirectory;
        }
    }
}
