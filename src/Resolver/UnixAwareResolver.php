<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Location\Resolver;

use FFI\Location\Internal\CacheReader;
use FFI\Location\Internal\ConfigReader;
use FFI\Location\Internal\ReaderInterface;

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

    /**
     * @return ReaderInterface|null
     */
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
