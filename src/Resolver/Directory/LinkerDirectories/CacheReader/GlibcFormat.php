<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader;

/**
 * The glibc cache format `glibc-ld.so.cache1.1`. It is the only format written
 * by the modern glibc, and the `compat` caches contain it after the legacy part.
 *
 * The header is followed by the entries. The key and the value of the entry are
 * offsets, relative to the header, of the library name and its pathname.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader
 */
final class GlibcFormat implements FormatInterface
{
    /**
     * Magic and version at the start of the header.
     */
    private const SIGNATURE = 'glibc-ld.so.cache1.1';

    /**
     * Offset of the entry count in the header.
     */
    private const COUNT_OFFSET = 20;

    /**
     * Size of the header: signature, entry count, strings length and reserved fields.
     */
    private const HEADER_SIZE = 48;

    /**
     * Size of the entry: flags, key, value, OS version and hwcap.
     */
    private const ENTRY_SIZE = 24;

    /**
     * Offset of the value in the entry.
     */
    private const VALUE_OFFSET = 8;

    public function supports(Buffer $buffer): bool
    {
        return $buffer->findOffset(self::SIGNATURE) !== null;
    }

    public function getLibraries(Buffer $buffer): iterable
    {
        $start = $buffer->findOffset(self::SIGNATURE);

        if ($start === null) {
            return [];
        }

        return $buffer->readReferences(
            $buffer->readInt($start + self::COUNT_OFFSET),
            $start + self::HEADER_SIZE,
            self::ENTRY_SIZE,
            self::VALUE_OFFSET,
            $start,
        );
    }
}
