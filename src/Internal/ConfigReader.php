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
 * @internal ConfigReader is an internal library class, please do not use it in your code.
 * @psalm-internal FFI\Location
 */
final class ConfigReader implements ReaderInterface
{
    /**
     * @var string
     */
    private string $pathname;

    /**
     * @param string $pathname
     */
    public function __construct(string $pathname)
    {
        $this->pathname = $pathname;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Traversable
    {
        return $this->read($this->pathname);
    }

    /**
     * @param string $pathname
     * @return \Traversable<array-key, string>
     */
    private function read(string $pathname): \Traversable
    {
        if (! \is_file($pathname) || ! \is_readable($pathname)) {
            return;
        }

        $fp = \fopen($pathname, 'rb');

        while (! \feof($fp)) {
            $line = (string)\fgets($fp);

            switch (true) {
                case \str_starts_with($line, 'include'):
                    foreach (\glob(\trim(\substr($line, 8))) as $config) {
                        yield from $this->read($config);
                    }
                    break;

                case \str_starts_with($line, '/'):
                    yield \trim($line);
                    break;
            }
        }

        \fclose($fp);
    }
}
