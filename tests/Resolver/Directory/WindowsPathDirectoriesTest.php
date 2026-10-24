<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver\Directory;

use FFI\Location\Resolver\Directory\WindowsPathDirectories;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WindowsPathDirectories::class)]
final class WindowsPathDirectoriesTest extends TestCase
{
    public function testQuotedAndEmptyEntriesAreHandled(): void
    {
        $this->setEnv('PATH', 'C:\\one;;"C:\\Program Files\\two";"C:\\semi;colon";C:\\three');

        self::assertSame(
            ['C:\\one', 'C:\\Program Files\\two', 'C:\\semi;colon', 'C:\\three'],
            \iterator_to_array((new WindowsPathDirectories())->getDirectories(), false),
        );
    }

    public function testUnsetVariableHasNoDirectories(): void
    {
        $this->setEnv('PATH', null);

        self::assertSame([], \iterator_to_array((new WindowsPathDirectories())->getDirectories(), false));
    }
}
