<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory;

/**
 * The system directory and the Windows directory.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver
 */
final class WindowsSystemDirectories implements DirectoryProviderInterface
{
    public function getDirectories(): iterable
    {
        $root = Environment::findValue('SystemRoot') ?? Environment::findValue('windir');

        if ($root === null) {
            return;
        }

        yield $root . '\\System32';
        yield $root;
    }
}
