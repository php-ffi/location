<?php

declare(strict_types=1);

namespace FFI\Location\Resolver\Directory\LinkerDirectories;

/**
 * Library directories listed by a source of the dynamic linker configuration.
 *
 * @internal this is an internal library interface, please do not use it in your code
 * @psalm-internal FFI\Location\Resolver
 *
 * @template-extends \IteratorAggregate<array-key, string>
 */
interface ReaderInterface extends \IteratorAggregate {}
