<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory;

/**
 * Directories listed in the environment variable, like `LD_LIBRARY_PATH`.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver
 */
final class EnvironmentDirectories implements DirectoryProviderInterface
{
    /**
     * @readonly
     *
     * @var non-empty-string
     */
    private string $variable;

    /**
     * @readonly
     *
     * @var non-empty-string
     */
    private string $delimiters;

    /**
     * @param non-empty-string $variable
     * @param non-empty-string $delimiters each character is a separate delimiter
     */
    public function __construct(string $variable, string $delimiters = ':')
    {
        $this->variable = $variable;
        $this->delimiters = $delimiters;
    }

    public function getDirectories(): iterable
    {
        $value = Environment::findValue($this->variable);

        if ($value === null) {
            return;
        }

        $pattern = '/[' . \preg_quote($this->delimiters, '/') . ']/';
        $directories = \preg_split($pattern, $value, -1, \PREG_SPLIT_NO_EMPTY);

        if ($directories !== false) {
            yield from $directories;
        }
    }
}
