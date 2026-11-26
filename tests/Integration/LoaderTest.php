<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Integration;

use FFI\Location\Locator;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Compares the result of {@see Locator::pathname()} with the file the operating
 * system actually loads for `FFI::cdef()`, in a new PHP process for each case.
 */
#[CoversClass(Locator::class)]
final class LoaderTest extends TestCase
{
    protected function setUp(): void
    {
        if (!\extension_loaded('ffi')) {
            self::markTestSkipped('Requires ext-ffi');
        }

        parent::setUp();
    }

    public function testSystemLibraryIsTheLoadedFile(): void
    {
        $names = match (\PHP_OS_FAMILY) {
            'Windows' => ['kernel32', 'user32.dll', 'msvcrt'],
            'Darwin' => [],
            default => ['libc.so.6', 'libm.so.6'],
        };

        if ($names === []) {
            self::markTestSkipped('The system libraries of macOS are not files since macOS 11');
        }

        foreach ($names as $name) {
            $result = self::probe($name);

            self::assertNull($result['error'], $name);
            self::assertNotNull($result['located'], $name);
            self::assertLoaded($result['located'], $result['files'], $name);
        }
    }

    public function testLibraryInSearchPathVariableIsTheLoadedFile(): void
    {
        $directory = $this->createDirectory();
        $library = $this->copyLibrary($directory);

        $result = self::probe(\basename($library), [self::getSearchPathVariable() => $directory]);

        self::assertNull($result['error']);
        self::assertSame($library, $result['located']);
        self::assertLoaded($library, $result['files']);
    }

    public function testLibraryInWorkingDirectoryIsFoundOnlyOnWindows(): void
    {
        $directory = $this->createDirectory();
        $library = $this->copyLibrary($directory);

        $result = self::probe(\basename($library), [], $directory);

        if (\PHP_OS_FAMILY === 'Windows') {
            self::assertNull($result['error']);
            self::assertSame($library, $result['located']);
            self::assertLoaded($library, $result['files']);
        } else {
            // Neither the linker nor the package searches the working directory
            self::assertNotNull($result['error']);
            self::assertNull($result['located']);
        }
    }

    public function testDllOfAnotherArchitectureIsSkippedLikeTheLoaderDoes(): void
    {
        $foreign = (string) \getenv('SystemRoot') . '\\SysWOW64\\version.dll';

        if (\PHP_OS_FAMILY !== 'Windows' || \PHP_INT_SIZE !== 8 || !\is_file($foreign)) {
            self::markTestSkipped('Requires 64-bit PHP on 64-bit Windows');
        }

        [$bits32, $bits64] = [$this->createDirectory(), $this->createDirectory()];
        $name = 'ffi-location-' . \bin2hex(\random_bytes(4)) . '.dll';
        $this->createFile($bits32 . '\\' . $name, (string) \file_get_contents($foreign));
        $library = $this->createFile($bits64 . '\\' . $name, (string) \file_get_contents(
            (string) \getenv('SystemRoot') . '\\System32\\version.dll',
        ));

        $result = self::probe($name, ['PATH' => $bits32 . ';' . $bits64]);

        self::assertNull($result['error']);
        self::assertSame($library, $result['located']);
        self::assertLoaded($library, $result['files']);
    }

    /**
     * Copies a real library of the operating system under a unique name.
     */
    private function copyLibrary(string $directory): string
    {
        [$source, $extension] = match (\PHP_OS_FAMILY) {
            'Windows' => [(string) \getenv('SystemRoot') . '\\System32\\version.dll', '.dll'],
            'Darwin' => [self::findDarwinLibrary(), '.dylib'],
            default => [(string) \realpath((string) Locator::pathname('libz.so.1') ?: (string) Locator::pathname('libm.so.6')), '.so'],
        };

        if ($source === '' || !\is_file($source)) {
            self::markTestSkipped('No library to copy');
        }

        $name = 'ffi-location-' . \bin2hex(\random_bytes(4)) . $extension;

        return $this->createFile($directory . \DIRECTORY_SEPARATOR . $name, (string) \file_get_contents($source));
    }

    /**
     * A dylib on disk, the system ones are not files since macOS 11.
     */
    private static function findDarwinLibrary(): string
    {
        foreach ([\dirname(\PHP_BINARY, 2) . '/lib', '/opt/homebrew/lib', '/usr/local/lib'] as $directory) {
            foreach (\glob($directory . '/*.dylib') ?: [] as $library) {
                if (!\is_link($library) && \is_file($library)) {
                    return $library;
                }
            }
        }

        return '';
    }

    private static function getSearchPathVariable(): string
    {
        return match (\PHP_OS_FAMILY) {
            'Windows' => 'PATH',
            'Darwin' => 'DYLD_LIBRARY_PATH',
            default => 'LD_LIBRARY_PATH',
        };
    }

    /**
     * Runs `probe.php` in a new PHP process.
     *
     * @param array<string, string> $environment the variables to set, prepended to `PATH`
     * @return array{located: string|null, error: string|null, files: list<string>}
     */
    private static function probe(string $name, array $environment = [], ?string $directory = null): array
    {
        $variables = \getenv();

        foreach ($environment as $variable => $value) {
            // The names are case-insensitive on Windows, like `Path` and `PATH`
            foreach (\array_keys($variables) as $existing) {
                if (\PHP_OS_FAMILY === 'Windows' && $existing !== $variable && \strcasecmp($existing, $variable) === 0) {
                    $variables[$variable] = $variables[$existing];
                    unset($variables[$existing]);
                }
            }

            $variables[$variable] = $variable === 'PATH' ? $value . ';' . ($variables['PATH'] ?? '') : $value;
        }

        $command = [\PHP_BINARY, '-d', 'ffi.enable=1'];

        // A build without php.ini, like in the tests of thread-safe builds, needs the extension explicitly
        if (\php_ini_loaded_file() === false) {
            \array_push($command, '-d', 'extension_dir=' . \ini_get('extension_dir'), '-d', 'extension=ffi');
        }

        \array_push($command, __DIR__ . '/probe.php', $name);

        $process = \proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $directory, $variables);
        self::assertIsResource($process);

        $output = (string) \stream_get_contents($pipes[1]);
        $errors = (string) \stream_get_contents($pipes[2]);
        \proc_close($process);

        /** @var array{located: string|null, error: string|null, files: list<string>}|null $result */
        $result = \json_decode($output, true);
        self::assertIsArray($result, $output . $errors);

        return $result;
    }

    /**
     * @param list<string> $files
     */
    private static function assertLoaded(string $library, array $files, string $message = ''): void
    {
        $normalize = static fn(string $file): string => \PHP_OS_FAMILY === 'Windows'
            ? \strtolower((string) \realpath($file))
            : (string) \realpath($file);

        self::assertContains($normalize($library), \array_map($normalize, $files), $message);
    }
}
