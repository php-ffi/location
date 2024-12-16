<?php

declare(strict_types=1);

namespace FFI\Location\Resolver;

use FFI\Location\Internal\CacheReader;
use FFI\Location\Internal\ConfigReader;
use FFI\Location\Internal\ReaderInterface;

/**
 * @internal unixAwareResolver is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver
 */
abstract class UnixAwareResolver extends PathResolver
{
    /**
     * Memoized linker directories.
     *
     * @var array<string>
     */
    private array $linkerDirectories = [];

    /**
     * @return iterable<string>
     */
    protected function getLinkerDirectories(): iterable
    {
        if ($this->linkerDirectories === [] && ($reader = $this->getReader())) {
            foreach ($reader as $directory) {
                $this->linkerDirectories[] = $directory;
            }
        }

        yield from $this->linkerDirectories;
    }

    private function getReader(): ?ReaderInterface
    {
        if (\is_file('/etc/ld.so.conf')) {
            return new ConfigReader('/etc/ld.so.conf');
        }

        if (\is_file('/etc/ld.so.cache')) {
            return new CacheReader('/etc/ld.so.cache');
        }

        return null;
    }
}
