<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Location\Internal;

/**
 * @template-extends \IteratorAggregate<array-key, string>
 *
 * @internal ReaderInterface is an internal library interface, please do not use it in your code.
 * @psalm-internal FFI\Location\Internal
 */
interface ReaderInterface extends \IteratorAggregate
{
}
