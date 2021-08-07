<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Location;

use FFI\Location\Resolver\LinuxPathResolver;
use FFI\Location\Resolver\MacOSPathResolver;
use FFI\Location\Resolver\PathResolverInterface;
use FFI\Location\Resolver\WindowsPathResolver;

final class Locator
{
    /**
     * @var PathResolverInterface|null
     */
    private static ?PathResolverInterface $resolver = null;

    /**
     * @return PathResolverInterface
     */
    private static function resolver(): PathResolverInterface
    {
        if (self::$resolver === null) {
            switch (\PHP_OS_FAMILY) {
                case 'Windows':
                    return self::$resolver = new WindowsPathResolver();

                case 'Darwin':
                    return self::$resolver = new MacOSPathResolver();

                default:
                    return self::$resolver = new LinuxPathResolver();
            }
        }

        return self::$resolver;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function exists(string $name): bool
    {
        return self::pathname($name) !== null;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public static function pathname(string $name): ?string
    {
        if (\is_file($name)) {
            return \realpath($name) ?: $name;
        }

        $resolver = self::resolver();

        return $resolver->resolve($name);
    }

    /**
     * @param string ...$libraries
     * @return string|null
     */
    public static function resolve(string ...$libraries): ?string
    {
        foreach ($libraries as $library) {
            if (($pathname = self::pathname($library)) !== null) {
                return $pathname;
            }
        }

        return null;
    }
}
