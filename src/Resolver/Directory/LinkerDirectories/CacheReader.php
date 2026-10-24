<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory\LinkerDirectories;

use FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader\Buffer;
use FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader\FormatInterface;
use FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader\GlibcFormat;
use FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader\LegacyFormat;

/**
 * Reads library directories from the binary linker cache, like `ld.so.cache`.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver
 */
final class CacheReader implements ReaderInterface
{
    /**
     * @readonly
     */
    private string $pathname;

    /**
     * @readonly
     *
     * @var list<FormatInterface>
     */
    private array $formats;

    /**
     * @param list<FormatInterface>|null $formats supported formats, the first one that recognizes the cache is used
     */
    public function __construct(string $pathname, ?array $formats = null)
    {
        $this->pathname = $pathname;
        $this->formats = $formats ?? [new GlibcFormat(), new LegacyFormat()];
    }

    /**
     * @return \Generator<array-key, string>
     */
    public function getIterator(): \Generator
    {
        $contents = \is_file($this->pathname) ? @\file_get_contents($this->pathname) : false;

        if ($contents === false) {
            return;
        }

        $buffer = new Buffer($contents);

        foreach ($this->formats as $format) {
            if ($format->supports($buffer)) {
                yield from $this->readDirectories($format->getLibraries($buffer));

                return;
            }
        }
    }

    /**
     * Returns unique directories of the libraries, other entries are not libraries.
     *
     * @param iterable<array-key, string> $libraries
     * @return \Generator<array-key, string>
     */
    private function readDirectories(iterable $libraries): \Generator
    {
        $seen = [];

        foreach ($libraries as $library) {
            if (\strncmp($library, '/', 1) !== 0) {
                continue;
            }

            $directory = \dirname($library);

            if (!isset($seen[$directory])) {
                $seen[$directory] = true;

                yield $directory;
            }
        }
    }
}
