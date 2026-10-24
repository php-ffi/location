<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader;

/**
 * The legacy glibc cache format `ld.so-1.7.0`.
 *
 * The header is followed by the entries and then by the strings. The key and
 * the value of the entry are offsets, relative to the strings, of the library
 * name and its pathname.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader
 */
final class LegacyFormat implements FormatInterface
{
    /**
     * Magic at the start of the header.
     */
    private const SIGNATURE = 'ld.so-1.7.0';

    /**
     * Offset of the entry count in the header.
     */
    private const COUNT_OFFSET = 12;

    /**
     * Size of the header: signature, padding and entry count.
     */
    private const HEADER_SIZE = 16;

    /**
     * Size of the entry: flags, key and value.
     */
    private const ENTRY_SIZE = 12;

    /**
     * Offset of the value in the entry.
     */
    private const VALUE_OFFSET = 8;

    public function supports(Buffer $buffer): bool
    {
        return $buffer->hasPrefix(self::SIGNATURE);
    }

    public function getLibraries(Buffer $buffer): iterable
    {
        $count = $buffer->readInt(self::COUNT_OFFSET);

        return $buffer->readReferences(
            $count,
            self::HEADER_SIZE,
            self::ENTRY_SIZE,
            self::VALUE_OFFSET,
            self::HEADER_SIZE + $count * self::ENTRY_SIZE,
        );
    }
}
