<?php

declare(strict_types=1);

namespace FFI\Location\Resolver;

use FFI\Location\Resolver\Directory\DirectoryProviderInterface;
use FFI\Location\Resolver\Naming\NamingInterface;

/**
 * Searches the library in the directories of the providers, in order of their priority.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location
 */
final class PathResolver implements PathResolverInterface
{
    /**
     * @readonly
     */
    private NamingInterface $naming;

    /**
     * @readonly
     *
     * @var list<DirectoryProviderInterface>
     */
    private array $providers;

    public function __construct(NamingInterface $naming, DirectoryProviderInterface ...$providers)
    {
        $this->naming = $naming;
        $this->providers = \array_values($providers);
    }

    public function resolve(string $name): ?string
    {
        foreach ($this->getCandidates($name) as $pathname) {
            if (\is_file($pathname)) {
                $real = \realpath($pathname);

                return $real === false ? $pathname : $real;
            }
        }

        return null;
    }

    /**
     * @param non-empty-string $name
     * @return \Generator<int, non-empty-string>
     */
    private function getCandidates(string $name): \Generator
    {
        $files = $this->naming->getFiles($name);

        if ($this->naming->isPath($name)) {
            yield from $files;

            return;
        }

        foreach ($this->getDirectories() as $directory) {
            foreach ($files as $file) {
                yield $directory . '/' . $file;
            }
        }
    }

    /**
     * @return \Generator<int, non-empty-string>
     */
    private function getDirectories(): \Generator
    {
        foreach ($this->providers as $provider) {
            foreach ($provider->getDirectories() as $directory) {
                // Skips empty directories
                if ($directory !== '') {
                    yield $directory;
                }
            }
        }
    }
}
