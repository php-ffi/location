<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver\Directory;

use FFI\Location\Resolver\Directory\WindowsSystemDirectories;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WindowsSystemDirectories::class)]
final class WindowsSystemDirectoriesTest extends TestCase
{
    /**
     * @return list<string>
     */
    private function getDirectories(): array
    {
        return \iterator_to_array((new WindowsSystemDirectories())->getDirectories(), false);
    }

    public function testSystemAndWindowsDirectoriesAreReturned(): void
    {
        $this->setEnv('SystemRoot', 'C:\\Windows');

        self::assertSame(['C:\\Windows\\System32', 'C:\\Windows'], $this->getDirectories());
    }

    public function testWindirVariableIsUsedWithoutSystemRoot(): void
    {
        $this->setEnv('SystemRoot', null);
        $this->setEnv('windir', 'D:\\Win');

        self::assertSame(['D:\\Win\\System32', 'D:\\Win'], $this->getDirectories());
    }

    public function testNoDirectoriesWithoutVariables(): void
    {
        $this->setEnv('SystemRoot', null);
        $this->setEnv('windir', null);

        self::assertSame([], $this->getDirectories());
    }
}
