<?php

declare(strict_types=1);

namespace FFI\Location\Internal;

/**
 * @internal readerInterface is an internal library interface, please do not use it in your code
 * @psalm-internal FFI\Location\Internal
 *
 * @template-extends \IteratorAggregate<array-key, string>
 */
interface ReaderInterface extends \IteratorAggregate {}
