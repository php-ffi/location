<?php

declare(strict_types=1);

namespace FFI\Location\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var list<string>
     */
    private array $paths = [];

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
        // Empty when the test is skipped before the setup
        if ($this->workingDirectory !== '') {
            \chdir($this->workingDirectory);
        }

        foreach ($this->environment as $name => $value) {
            \putenv($value === false ? $name : $name . '=' . $value);
        }

        foreach (\array_reverse($this->paths) as $path) {
            \is_dir($path) ? @\rmdir($path) : @\unlink($path);
        }

        parent::tearDown();
    }

    /**
     * Creates a temporary directory that is removed after the test.
     */
    protected function createDirectory(): string
    {
        $directory = \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'ffi-location-' . \bin2hex(\random_bytes(6));
        \mkdir($directory);

        return $this->paths[] = (string) \realpath($directory);
    }

    /**
     * Creates a file that is removed after the test.
     */
    protected function createFile(string $pathname, string $contents): string
    {
        \file_put_contents($pathname, $contents);

        return $this->paths[] = $pathname;
    }

    /**
     * Sets the environment variable until the end of the test.
     */
    protected function setEnvironment(string $name, ?string $value): void
    {
        if (!\array_key_exists($name, $this->environment)) {
            $this->environment[$name] = \getenv($name, true);
        }

        \putenv($value === null ? $name : $name . '=' . $value);
    }

    /**
     * The beginning of an ELF object of the given class, `1` for 32 bit and `2` for 64 bit.
     */
    protected static function getElf(int $class): string
    {
        return "\x7FELF" . \chr($class) . \str_repeat("\0", 59);
    }

    /**
     * The ELF object of the class of the PHP process.
     */
    protected static function getNativeElf(): string
    {
        return self::getElf(\PHP_INT_SIZE === 8 ? 2 : 1);
    }

    /**
     * The beginning of a PE image with the given `Machine` field.
     */
    protected static function getPe(int $machine): string
    {
        return 'MZ' . \str_repeat("\0", 0x3A) . \pack('V', 0x40) . "PE\0\0" . \pack('v', $machine) . \str_repeat("\0", 18);
    }

    /**
     * The PE image of the architecture of the PHP process.
     */
    protected static function getNativePe(): string
    {
        return self::getPe(\PHP_INT_SIZE === 8 ? 0x8664 : 0x014C);
    }
}
