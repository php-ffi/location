<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver\Directory\LinkerDirectories\CacheReader;

/**
 * Builds binary linker caches of the glibc formats.
 */
final class CacheBuilder
{
    /**
     * @param list<string> $libraries pathnames of the libraries
     * @param string $legacy data placed before the cache, like the legacy part of the `compat` cache
     */
    public static function createGlibcCache(array $libraries, string $legacy = ''): string
    {
        $strings = '';
        $entries = '';
        $offset = 48 + \count($libraries) * 24;

        foreach ($libraries as $library) {
            $entries .= \pack('lLLL', 1, 0, $offset + \strlen($strings), 0) . \str_repeat("\0", 8);
            $strings .= $library . "\0";
        }

        $header = 'glibc-ld.so.cache1.1' . \pack('LL', \count($libraries), \strlen($strings)) . \str_repeat("\0", 20);

        return $legacy . $header . $entries . $strings;
    }

    /**
     * @param list<string> $libraries pathnames of the libraries
     */
    public static function createLegacyCache(array $libraries): string
    {
        $strings = '';
        $entries = '';

        foreach ($libraries as $library) {
            $entries .= \pack('lLL', 1, 0, \strlen($strings));
            $strings .= $library . "\0";
        }

        return "ld.so-1.7.0\0" . \pack('L', \count($libraries)) . $entries . $strings;
    }
}
