<?php

declare(strict_types=1);

namespace FFI\Location\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var list<string>
     */
    private array $files = [];

    /**
     * @var list<string>
     */
    private array $directories = [];

    /**
     * @var array<string, string|false>
     */
    private array $environment = [];

    private string $workingDirectory = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->workingDirectory = (string) \getcwd();
    }

    protected function tearDown(): void
    {
        \chdir($this->workingDirectory);

        foreach ($this->environment as $name => $value) {
            \putenv($value === false ? $name : $name . '=' . $value);
        }

        foreach ($this->files as $file) {
            @\unlink($file);
        }

        foreach (\array_reverse($this->directories) as $directory) {
            @\rmdir($directory);
        }

        parent::tearDown();
    }

    /**
     * Creates a temporary directory that is removed after the test.
     */
    protected function createDirectory(): string
    {
        $pathname = \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'ffi-location-' . \bin2hex(\random_bytes(6));

        \mkdir($pathname);

        $this->directories[] = $pathname;

        return (string) \realpath($pathname);
    }

    /**
     * Creates a file that is removed after the test, and returns its real pathname.
     */
    protected function createFile(string $pathname, string $contents = ''): string
    {
        $directory = \dirname($pathname);

        if (!\is_dir($directory)) {
            \mkdir($directory, 0777, true);
            $this->directories[] = $directory;
        }

        \file_put_contents($pathname, $contents);

        $this->files[] = $pathname;

        return (string) \realpath($pathname);
    }

    /**
     * Schedules the removal of a file that is created by the test.
     */
    protected function trackFile(string $pathname): void
    {
        $this->files[] = $pathname;
    }

    /**
     * Sets (or unsets, if the value is `null`) the environment variable until the end of the test.
     */
    protected function setEnv(string $name, ?string $value): void
    {
        if (!\array_key_exists($name, $this->environment)) {
            $this->environment[$name] = \getenv($name);
        }

        \putenv($value === null ? $name : $name . '=' . $value);
    }

    protected function createUniqueName(string $suffix = '.lib'): string
    {
        return 'ffi-location-' . \bin2hex(\random_bytes(6)) . $suffix;
    }

    protected function skipOnWindows(): void
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('Unix-style paths are not supported on Windows.');
        }
    }
}
