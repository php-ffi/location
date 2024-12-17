<?php

declare(strict_types=1);

namespace FFI\Location;

use FFI\Location\Resolver\LinuxPathResolver;
use FFI\Location\Resolver\MacOSPathResolver;
use FFI\Location\Resolver\PathResolverInterface;
use FFI\Location\Resolver\WindowsPathResolver;

final class Locator
{
    private static ?PathResolverInterface $resolver = null;

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

    public static function exists(string $name): bool
    {
        return self::pathname($name) !== null;
    }

    /**
     * @return non-empty-string|null
     */
    public static function pathname(string $name): ?string
    {
        if ($name === '') {
            return null;
        }

        if (\is_file($name)) {
            /**
             * Allow non-strict short ternary operator
             *
             * @var non-empty-string
             *
             * @phpstan-ignore ternary.shortNotAllowed
             */
            return \realpath($name) ?: $name;
        }

        $resolver = self::resolver();

        return $resolver->resolve($name);
    }

    /**
     * @return non-empty-string|null
     */
    public static function resolve(string ...$libraries): ?string
    {
        foreach ($libraries as $library) {
            $pathname = self::pathname($library);

            if ($pathname !== null) {
                return $pathname;
            }
        }

        return null;
    }
}
