<?php

declare(strict_types=1);

namespace FFI\Location\Resolver;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location
 */
abstract class PathResolver implements PathResolverInterface
{
    /**
     * @var array<non-empty-string, non-empty-string|null>
     */
    private array $paths = [];

    /**
     * @return iterable<array-key, string>
     */
    abstract protected function getLibDirectories(): iterable;

    public function resolve(string $name): ?string
    {
        if (!isset($this->paths[$name])) {
            foreach ($this->getLibDirectories() as $directory) {
                $pathname = $directory . '/' . $name;

                if (\is_file($pathname)) {
                    // Allow non-strict short ternary operator
                    // @phpstan-ignore ternary.shortNotAllowed
                    return $this->paths[$name] = \realpath($pathname) ?: $pathname;
                }
            }

            return $this->paths[$name] = null;
        }

        return $this->paths[$name];
    }

    /**
     * @param non-empty-string $env
     * @param non-empty-string $delimiter
     *
     * @return iterable<array-key, non-empty-string>
     */
    protected function getEnvDirectories(string $env, string $delimiter = ':'): iterable
    {
        $value = $this->fetchEnvVariable($env);

        if ($value === null || $value === '') {
            return [];
        }

        foreach (\explode($delimiter, $value) as $path) {
            $trimmed = \trim($path);

            if ($trimmed !== '') {
                yield $trimmed;
            }
        }
    }

    /**
     * @param non-empty-string $variable
     */
    private function fetchEnvVariable(string $variable): ?string
    {
        $value = $_ENV[$variable] ?? null;

        if (\is_string($value)) {
            return $value;
        }

        $value = $_SERVER[$variable] ?? null;

        if (\is_string($value)) {
            return $value;
        }

        $value = \getenv($variable);

        if (\is_string($value)) {
            return $value;
        }

        return null;
    }
}
