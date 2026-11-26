<?php

/**
 * Runs in a child process: locates the library by the name, loads it with
 * `FFI::cdef()` and prints a JSON with the located pathname and the files the
 * process has loaded, as reported by the operating system.
 *
 * Usage: php probe.php <name>
 */

declare(strict_types=1);

use FFI\Location\Locator;

require \dirname(__DIR__, 2) . '/vendor/autoload.php';

$name = $argv[1] ?? '';

$result = [
    'located' => Locator::pathname($name),
    'error' => null,
    'files' => [],
];

try {
    $library = FFI::cdef('', $name);
} catch (FFI\Exception $e) {
    $result['error'] = $e->getMessage();
}

$result['files'] = match (\PHP_OS_FAMILY) {
    'Windows' => getWindowsModule($name),
    'Darwin' => getDarwinImages(),
    default => getLinuxMappings(),
};

echo \json_encode($result, \JSON_THROW_ON_ERROR);

/**
 * The file of the module loaded under the name.
 *
 * @return list<string>
 */
function getWindowsModule(string $name): array
{
    $kernel32 = FFI::cdef(<<<'C'
        void *GetModuleHandleA(const char *name);
        unsigned long GetModuleFileNameA(void *module, char *buffer, unsigned long size);
        C, 'kernel32.dll');

    $file = \basename(\str_replace('\\', '/', $name));
    $module = $kernel32->GetModuleHandleA(\str_contains($file, '.') ? $name : $name . '.dll');

    if ($module === null) {
        return [];
    }

    $buffer = $kernel32->new('char[1024]');
    $length = $kernel32->GetModuleFileNameA($module, $buffer, 1024);

    return [FFI::string($buffer, $length)];
}

/**
 * The files mapped into the memory of the process.
 *
 * @return list<string>
 */
function getLinuxMappings(): array
{
    $files = [];

    foreach (\file('/proc/self/maps', \FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        if (\preg_match('#\s(/\S.*)$#', $line, $matches) === 1) {
            $files[$matches[1]] = true;
        }
    }

    return \array_keys($files);
}

/**
 * The images loaded by dyld.
 *
 * @return list<string>
 */
function getDarwinImages(): array
{
    $dyld = FFI::cdef(<<<'C'
        uint32_t _dyld_image_count(void);
        const char *_dyld_get_image_name(uint32_t index);
        C);

    $files = [];

    for ($i = 0, $count = $dyld->_dyld_image_count(); $i < $count; ++$i) {
        $files[] = (string) $dyld->_dyld_get_image_name($i);
    }

    return $files;
}
