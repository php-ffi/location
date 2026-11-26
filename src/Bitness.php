<?php

declare(strict_types=1);

namespace FFI\Location;

/**
 * Bitness of the libraries that are accepted by the search.
 */
enum Bitness
{
    /**
     * The bitness of the PHP process, the only one `FFI::cdef()` can load.
     */
    case Auto;

    /**
     * 32-bit libraries only.
     */
    case Bits32;

    /**
     * 64-bit libraries (x86-64 and ARM64) only.
     */
    case Bits64;

    /**
     * Tells whether a library of the given bitness is accepted.
     *
     * @param bool $is64Bit whether the library is 64-bit
     */
    public function accepts(bool $is64Bit): bool
    {
        return match ($this) {
            self::Auto => $is64Bit === (\PHP_INT_SIZE === 8),
            self::Bits32 => !$is64Bit,
            self::Bits64 => $is64Bit,
        };
    }
}
