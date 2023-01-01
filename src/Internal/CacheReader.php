<?php

declare(strict_types=1);

namespace FFI\Location\Internal;

/**
 * @internal CacheReader is an internal library class, please do not use it in your code.
 * @psalm-internal FFI\Location
 */
final class CacheReader implements ReaderInterface
{
    /**
     * @var string
     */
    private const EXEC_CMD = 'strings -n5 %s';

    /**
     * @var array<string>
     */
    private array $paths = [];

    /**
     * @var string
     */
    private string $pathname;

    /**
     * @param string $pathname
     */
    public function __construct(string $pathname)
    {
        $this->pathname = $pathname;
    }

    /**
     * @return string
     */
    private function exec(): string
    {
        if (! \is_file($this->pathname)) {
            return '';
        }

        if (! \function_exists('\\shell_exec')) {
            return '';
        }

        /** @psalm-suppress ForbiddenCode */
        $output = @\shell_exec(\sprintf(self::EXEC_CMD, \escapeshellarg($this->pathname)));

        if (! \is_string($output)) {
            return '';
        }

        return $output;
    }

    /**
     * @return array<string>
     */
    private function collect(): array
    {
        $paths = [];

        foreach (\explode("\n", $this->exec()) as $library) {
            $directory = \dirname($library);
            $isDirectory = $directory !== '.' && $directory !== '';

            if ($isDirectory && ! \in_array($directory, $paths, true)) {
                $paths[] = $directory;
            }
        }

        return $paths;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Traversable
    {
        if (! \is_file($this->pathname)) {
            return new \EmptyIterator();
        }

        if ($this->paths === []) {
            $this->paths = $this->collect();
        }

        return new \ArrayIterator($this->paths);
    }
}
