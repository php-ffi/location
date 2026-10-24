<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory;

use FFI\Location\Resolver\Directory\LinkerDirectories\ReaderInterface;

/**
 * Directories of the dynamic linker configuration, read lazily in the order of the readers.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver
 */
final class LinkerDirectories implements DirectoryProviderInterface
{
    /**
     * @readonly
     *
     * @var list<ReaderInterface>
     */
    private array $readers;

    /**
     * @var array<int, list<string>>
     */
    private array $directories = [];

    public function __construct(ReaderInterface ...$readers)
    {
        $this->readers = \array_values($readers);
    }

    public function getDirectories(): iterable
    {
        foreach ($this->readers as $index => $reader) {
            $this->directories[$index] = $this->directories[$index] ?? \iterator_to_array($reader, false);

            yield from $this->directories[$index];
        }
    }
}
