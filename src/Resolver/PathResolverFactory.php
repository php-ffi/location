<?php

declare(strict_types=1);

namespace FFI\Location\Resolver;

use FFI\Location\Resolver\Directory\ApplicationDirectory;
use FFI\Location\Resolver\Directory\EnvironmentDirectories;
use FFI\Location\Resolver\Directory\FixedDirectories;
use FFI\Location\Resolver\Directory\LinkerDirectories;
use FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader;
use FFI\Location\Resolver\Directory\LinkerDirectories\ConfigReader;
use FFI\Location\Resolver\Directory\WindowsPathDirectories;
use FFI\Location\Resolver\Directory\WindowsSystemDirectories;
use FFI\Location\Resolver\Directory\WorkingDirectory;
use FFI\Location\Resolver\Naming\UnixNaming;
use FFI\Location\Resolver\Naming\WindowsNaming;

/**
 * Creates the resolver with the library search order of the platform. The directories
 * of the dynamic linker (`LoadLibrary`, `dlopen`) go first, the rest are fallbacks.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Location
 */
final class PathResolverFactory
{
    /**
     * @param string $family value of the `\PHP_OS_FAMILY` constant
     */
    public static function create(string $family): PathResolverInterface
    {
        switch ($family) {
            case 'Windows':
                return self::createWindowsResolver();

            case 'Darwin':
                return self::createMacOSResolver();

            default:
                return self::createLinuxResolver();
        }
    }

    private static function createWindowsResolver(): PathResolverInterface
    {
        return new PathResolver(
            new WindowsNaming(),
            new ApplicationDirectory(),
            new WindowsSystemDirectories(),
            new WorkingDirectory(),
            new WindowsPathDirectories(),
        );
    }

    private static function createMacOSResolver(): PathResolverInterface
    {
        return new PathResolver(
            new UnixNaming(),
            new EnvironmentDirectories('DYLD_LIBRARY_PATH'),
            new EnvironmentDirectories('DYLD_FALLBACK_LIBRARY_PATH'),
            // Default directories, including the Homebrew prefixes of Apple Silicon and Intel
            new FixedDirectories('/usr/local/lib', '/usr/lib', '/opt/homebrew/lib'),
            // Fallback
            new WorkingDirectory(),
        );
    }

    private static function createLinuxResolver(): PathResolverInterface
    {
        return new PathResolver(
            new UnixNaming(),
            // The glibc accepts a colon or a semicolon as a delimiter
            new EnvironmentDirectories('LD_LIBRARY_PATH', ':;'),
            new LinkerDirectories(
                new ConfigReader('/etc/ld.so.conf'),
                new CacheReader('/etc/ld.so.cache'),
            ),
            // Default directories, the 64-bit ones go first. The `/usr/local/lib` is the BSD prefix
            new FixedDirectories('/lib64', '/usr/lib64', '/lib', '/usr/lib', '/usr/local/lib'),
            // Fallback
            new WorkingDirectory(),
        );
    }
}
