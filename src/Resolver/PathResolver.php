<?php

declare(strict_types=1);

namespace FFI\Location\Resolver;

/**
 * @internal PathResolver is an internal library class, please do not use it in your code.
 * @psalm-internal FFI\Location\Resolver
 */
abstract class PathResolver implements PathResolverInterface
{
    /**
     * @var array<string, string|null>
     */
    private array $paths = [];

    /**
     * @return iterable<string>
     */
    abstract protected function getLibDirectories(): iterable;

    /**
     * @param string $name
     * @return string|null
     */
    public function resolve(string $name): ?string
    {
        if (! isset($this->paths[$name])) {
            foreach ($this->getLibDirectories() as $directory) {
                if (\is_file($pathname = $directory . '/' . $name)) {
                    return $this->paths[$name] = \realpath($pathname) ?: $pathname;
                }
            }

            return $this->paths[$name] = null;
        }

        return $this->paths[$name];
    }

    /**
     * @param string $env
     * @param string $delimiter
     * @return iterable<string>
     */
    protected function getEnvDirectories(string $env, string $delimiter = ':'): iterable
    {
        foreach (\explode($delimiter, (string)\getenv($env)) as $path) {
            if ($path = \trim($path)) {
                yield $path;
            }
        }
    }
}
