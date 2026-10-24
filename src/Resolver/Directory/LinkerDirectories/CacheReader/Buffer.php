<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader;

/**
 * Read-only binary data. Reading outside of the data gives an empty result.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader
 */
final class Buffer
{
    /**
     * @readonly
     */
    private string $contents;

    public function __construct(string $contents)
    {
        $this->contents = $contents;
    }

    /**
     * Returns the offset of the first occurrence of the sequence, or `null` if there is none.
     */
    public function findOffset(string $sequence): ?int
    {
        $offset = \strpos($this->contents, $sequence);

        return $offset === false ? null : $offset;
    }

    public function hasPrefix(string $sequence): bool
    {
        return \strncmp($this->contents, $sequence, \strlen($sequence)) === 0;
    }

    /**
     * Reads the unsigned 32-bit integer in the byte order of the machine.
     */
    public function readInt(int $offset): int
    {
        if ($offset < 0 || $offset + 4 > \strlen($this->contents)) {
            return 0;
        }

        $result = \unpack('Lvalue', $this->contents, $offset);

        if ($result === false || !\is_int($result['value'])) {
            return 0;
        }

        return $result['value'];
    }

    /**
     * Reads the null-terminated string.
     */
    public function readString(int $offset): string
    {
        if ($offset < 0 || $offset >= \strlen($this->contents)) {
            return '';
        }

        return \substr($this->contents, $offset, \strcspn($this->contents, "\0", $offset));
    }

    /**
     * Reads strings referenced by the table of fixed-size records.
     *
     * @param int $count declared number of the records
     * @param int $first offset of the first record
     * @param int $size size of the record
     * @param int $field offset of the 32-bit string reference in the record
     * @param int $base offset the string references are relative to
     * @return \Generator<int, string>
     */
    public function readReferences(int $count, int $first, int $size, int $field, int $base): \Generator
    {
        $count = \min($count, \intdiv(\max(0, \strlen($this->contents) - $first), $size));

        for ($i = 0; $i < $count; ++$i) {
            yield $this->readString($base + $this->readInt($first + $i * $size + $field));
        }
    }
}
